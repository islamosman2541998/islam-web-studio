<?php

namespace Tests\Feature;

use App\Filament\Pages\MetaAdsAnalytics;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\MetaLeads\MetaLeadResource;
use App\Filament\Widgets\MetaAdPerformanceTable;
use App\Filament\Widgets\MetaAdsDecisionOverview;
use App\Filament\Widgets\MetaAdsEfficiencyChart;
use App\Filament\Widgets\MetaAdsOverview;
use App\Filament\Widgets\MetaAdsTrendChart;
use App\Filament\Widgets\MetaCampaignPerformanceTable;
use App\Jobs\ImportMetaLead;
use App\Models\Lead;
use App\Models\MetaAdInsight;
use App\Models\User;
use App\Support\MetaAds;
use App\Support\Studio;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MetaAdsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        config([
            'services.meta.version' => 'v26.0',
            'services.meta.app_secret' => 'test-secret',
            'services.meta.access_token' => 'test-token',
            'services.meta.verify_token' => 'verify-me',
            'services.meta.ad_account_id' => '123456',
            'services.meta.currency' => 'EGP',
        ]);
    }

    public function test_meta_can_verify_the_webhook_subscription(): void
    {
        $this->get('/webhooks/meta/lead-ads?hub_mode=subscribe&hub_verify_token=verify-me&hub_challenge=98765')
            ->assertOk()
            ->assertSeeText('98765');

        $this->get('/webhooks/meta/lead-ads?hub_mode=subscribe&hub_verify_token=wrong&hub_challenge=98765')
            ->assertForbidden();
    }

    public function test_signed_lead_webhook_is_queued_and_invalid_signature_is_rejected(): void
    {
        Queue::fake();
        $payload = json_encode(['entry' => [[
            'changes' => [[
                'field' => 'leadgen',
                'value' => ['leadgen_id' => 'lead-123', 'page_id' => 'page-1', 'form_id' => 'form-1'],
            ]],
        ]]], JSON_THROW_ON_ERROR);
        $signature = 'sha256='.hash_hmac('sha256', $payload, 'test-secret');

        $this->call('POST', '/webhooks/meta/lead-ads', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_HUB_SIGNATURE_256' => $signature,
        ], $payload)->assertOk()->assertSeeText('EVENT_RECEIVED');

        Queue::assertPushed(ImportMetaLead::class, fn (ImportMetaLead $job): bool => $job->leadId === 'lead-123');

        $this->call('POST', '/webhooks/meta/lead-ads', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_HUB_SIGNATURE_256' => 'sha256=invalid',
        ], $payload)->assertForbidden();
    }

    public function test_meta_lead_is_imported_once_with_campaign_and_form_answers(): void
    {
        Http::fake([
            'graph.facebook.com/v26.0/lead-123*' => Http::response([
                'id' => 'lead-123',
                'created_time' => '2026-09-22T10:30:00+0000',
                'campaign_id' => 'campaign-1',
                'campaign_name' => 'Website leads',
                'adset_id' => 'adset-1',
                'adset_name' => 'Business owners',
                'ad_id' => 'ad-1',
                'ad_name' => 'Website creative A',
                'form_id' => 'form-1',
                'platform' => 'facebook',
                'field_data' => [
                    ['name' => 'full_name', 'values' => ['Ahmed Ali']],
                    ['name' => 'phone_number', 'values' => ['+201000000000']],
                    ['name' => 'email', 'values' => ['ahmed@example.com']],
                    ['name' => 'needed_service', 'values' => ['Online store']],
                ],
            ]),
        ]);

        $meta = app(MetaAds::class);
        $first = $meta->importLead('lead-123', ['page_id' => 'page-1']);
        $first->update(['status' => 'contacted']);
        $second = $meta->importLead('lead-123', ['page_id' => 'page-1']);

        $this->assertSame($first->id, $second->id);
        $this->assertSame('contacted', $second->status);
        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseHas('leads', [
            'meta_lead_id' => 'lead-123',
            'name' => 'Ahmed Ali',
            'source' => 'meta',
            'meta_campaign_name' => 'Website leads',
            'meta_ad_name' => 'Website creative A',
        ]);
        $this->assertStringContainsString('Needed Service: Online store', $first->message);
    }

    public function test_page_access_token_is_preferred_when_fetching_lead_details(): void
    {
        config(['services.meta.page_access_token' => 'page-token']);
        Http::fake([
            'graph.facebook.com/v26.0/lead-page-token*' => Http::response([
                'id' => 'lead-page-token',
                'field_data' => [
                    ['name' => 'full_name', 'values' => ['Page Lead']],
                ],
            ]),
        ]);

        app(MetaAds::class)->importLead('lead-page-token');

        Http::assertSent(fn ($request): bool => $request->hasHeader('Authorization', 'Bearer page-token'));
        $this->assertDatabaseHas('leads', ['meta_lead_id' => 'lead-page-token']);
    }

    public function test_leads_can_be_synchronized_from_page_forms_without_waiting_for_a_webhook(): void
    {
        config([
            'services.meta.page_id' => 'page-1',
            'services.meta.page_access_token' => 'page-token',
        ]);
        Http::fake([
            'graph.facebook.com/v26.0/page-1/leadgen_forms*' => Http::response([
                'data' => [['id' => 'form-1']],
            ]),
            'graph.facebook.com/v26.0/form-1/leads*' => Http::response([
                'data' => [['id' => 'lead-sync-1', 'created_time' => now()->toIso8601String()]],
            ]),
            'graph.facebook.com/v26.0/lead-sync-1*' => Http::response([
                'id' => 'lead-sync-1',
                'created_time' => now()->toIso8601String(),
                'form_id' => 'form-1',
                'field_data' => [
                    ['name' => 'full_name', 'values' => ['Synced Lead']],
                    ['name' => 'phone_number', 'values' => ['+201000000000']],
                ],
            ]),
        ]);

        $this->assertSame(1, app(MetaAds::class)->syncLeads());
        $this->assertDatabaseHas('leads', [
            'meta_lead_id' => 'lead-sync-1',
            'meta_page_id' => 'page-1',
            'meta_form_id' => 'form-1',
            'name' => 'Synced Lead',
        ]);
    }

    public function test_ad_insights_are_synchronized_and_updated_idempotently(): void
    {
        Http::fake([
            'graph.facebook.com/v26.0/act_123456/insights*' => Http::response([
                'data' => [[
                    'date_start' => today()->toDateString(),
                    'account_id' => '123456',
                    'account_name' => 'Studio Ads',
                    'campaign_id' => 'campaign-1',
                    'campaign_name' => 'Website leads',
                    'adset_id' => 'adset-1',
                    'adset_name' => 'Business owners',
                    'ad_id' => 'ad-1',
                    'ad_name' => 'Website creative A',
                    'impressions' => '1200',
                    'reach' => '900',
                    'clicks' => '55',
                    'inline_link_clicks' => '40',
                    'spend' => '300.00',
                    'cpc' => '5.4545',
                    'cpm' => '250.0000',
                    'ctr' => '4.5833',
                    'actions' => [['action_type' => 'lead', 'value' => '6']],
                    'cost_per_action_type' => [['action_type' => 'lead', 'value' => '50']],
                ]],
            ]),
        ]);

        $this->assertSame(1, app(MetaAds::class)->syncInsights(7));
        $this->assertSame(1, app(MetaAds::class)->syncInsights(7));
        $this->assertDatabaseCount('meta_ad_insights', 1);
        $insight = MetaAdInsight::firstOrFail();
        $this->assertSame(6, $insight->leads);
        $this->assertSame('50.0000', $insight->cost_per_lead);
        $this->assertSame(55, $insight->clicks);
    }

    public function test_meta_widgets_render_the_synced_ad_metrics_for_authorized_users(): void
    {
        Permission::findOrCreate('leads.view', 'web');
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo('leads.view');
        MetaAdInsight::create([
            'date' => today(),
            'account_id' => '123456',
            'campaign_id' => 'campaign-1',
            'campaign_name' => 'Website leads',
            'adset_id' => 'adset-1',
            'adset_name' => 'Business owners',
            'ad_id' => 'ad-1',
            'ad_name' => 'Website creative A',
            'impressions' => 1200,
            'reach' => 900,
            'clicks' => 55,
            'link_clicks' => 40,
            'leads' => 6,
            'spend' => 300,
            'cpc' => 5.4545,
            'cpm' => 250,
            'ctr' => 4.5833,
            'cost_per_lead' => 50,
            'currency' => 'EGP',
        ]);

        Livewire::actingAs($user)->test(MetaAdsOverview::class)
            ->assertSee(Studio::text('meta_ads_overview'))
            ->assertSee('300.00 EGP');

        Livewire::actingAs($user)->test(MetaAdsTrendChart::class)
            ->assertSee(Studio::text('meta_ads_trend'));

        Livewire::actingAs($user)->test(MetaAdsDecisionOverview::class)
            ->assertSee(Studio::text('meta_decision_metrics'))
            ->assertSee('4.58%')
            ->assertSee('50.00 EGP');

        Livewire::actingAs($user)->test(MetaAdsEfficiencyChart::class)
            ->assertSee(Studio::text('meta_efficiency_trend'));

        Livewire::actingAs($user)->test(MetaCampaignPerformanceTable::class)
            ->assertSee('Website leads')
            ->assertSee('50.00 EGP');

        Livewire::actingAs($user)->test(MetaAdPerformanceTable::class)
            ->assertSee('Website leads')
            ->assertSee('Website creative A')
            ->assertSee('50.00 EGP');
    }

    public function test_meta_widgets_stay_visible_with_empty_data_before_connection_is_configured(): void
    {
        Permission::findOrCreate('leads.view', 'web');
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo('leads.view');
        config([
            'services.meta.access_token' => null,
            'services.meta.ad_account_id' => null,
        ]);

        Livewire::actingAs($user)->test(MetaAdsOverview::class)
            ->assertSee(Studio::text('meta_ads_overview'))
            ->assertSee(Studio::text('meta_ads_not_configured'))
            ->assertSee('0.00 EGP');

        Livewire::actingAs($user)->test(MetaAdsTrendChart::class)
            ->assertSee(Studio::text('meta_ads_trend'));

        Livewire::actingAs($user)->test(MetaAdPerformanceTable::class)
            ->assertSee(Studio::text('meta_ads_per_ad'));
    }

    public function test_meta_has_a_separate_analytics_page_and_leads_are_isolated_from_site_enquiries(): void
    {
        Permission::findOrCreate('leads.view', 'web');
        Permission::findOrCreate('dashboard.view', 'web');
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo(['leads.view', 'dashboard.view']);
        Lead::factory()->create(['source' => 'form']);
        Lead::factory()->create(['source' => 'meta', 'meta_lead_id' => 'isolated-meta-lead']);

        $this->actingAs($user)
            ->get(MetaAdsAnalytics::getUrl())
            ->assertOk()
            ->assertSee(Studio::text('meta_analytics'))
            ->assertSee(Studio::text('analysis_period'));

        $this->actingAs($user)
            ->get(MetaLeadResource::getUrl('index'))
            ->assertOk()
            ->assertSee(Studio::text('meta_leads'));

        $this->assertSame(1, LeadResource::getEloquentQuery()->count());
        $this->assertSame(1, MetaLeadResource::getEloquentQuery()->count());
    }
}
