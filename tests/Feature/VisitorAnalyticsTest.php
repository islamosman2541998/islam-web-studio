<?php

namespace Tests\Feature;

use App\Filament\Pages\VisitorAnalytics;
use App\Filament\Widgets\RecentVisitorsTable;
use App\Filament\Widgets\TopPagesTable;
use App\Filament\Widgets\VisitorOverview;
use App\Filament\Widgets\VisitorSourcesChart;
use App\Filament\Widgets\VisitorTrendChart;
use App\Models\User;
use App\Models\VisitorEvent;
use App\Models\VisitorPageView;
use App\Models\VisitorSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class VisitorAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private string $visitorId;
    private string $sessionId;
    private string $pageviewId;

    protected function setUp(): void
    {
        parent::setUp();
        config(['studio.demo' => false]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->visitorId = (string) Str::uuid();
        $this->sessionId = (string) Str::uuid();
        $this->pageviewId = (string) Str::uuid();
    }

    public function test_page_view_is_collected_without_storing_raw_ip_or_query_strings(): void
    {
        $this->collect(['type' => 'pageview', 'path' => '/ar/work?secret=value', 'referrer' => 'https://google.com/search?q=private'])
            ->assertAccepted();

        $session = VisitorSession::firstOrFail();
        $this->assertSame('/ar/work', $session->landing_path);
        $this->assertSame('google.com', $session->referrer_host);
        $this->assertNull($session->ip_hash);
        $this->assertNull($session->user_agent);
        $this->assertNull($session->consented_at);
        $this->assertDatabaseHas('visitor_page_views', ['path' => '/ar/work', 'client_id' => $this->pageviewId]);
    }

    public function test_retries_are_idempotent_and_activity_only_moves_forward(): void
    {
        $payload = ['type' => 'pageview', 'path' => '/ar'];
        $this->collect($payload)->assertAccepted();
        $this->collect($payload)->assertAccepted();
        $this->assertDatabaseCount('visitor_page_views', 1);
        $this->assertSame(1, VisitorSession::firstOrFail()->page_views_count);

        $this->collect(['type' => 'activity', 'path' => '/ar', 'duration' => 35, 'scroll_depth' => 80])->assertAccepted();
        $this->collect(['type' => 'activity', 'path' => '/ar', 'duration' => 10, 'scroll_depth' => 20])->assertAccepted();
        $view = VisitorPageView::firstOrFail();
        $this->assertSame(35, $view->duration_seconds);
        $this->assertSame(80, $view->scroll_depth);
    }

    public function test_only_known_events_are_stored_without_form_values(): void
    {
        $this->collect(['type' => 'pageview', 'path' => '/ar/contact'])->assertAccepted();
        $eventId = (string) Str::uuid();
        $this->collect([
            'type' => 'event',
            'path' => '/ar/contact',
            'event_id' => $eventId,
            'event' => 'form_submit',
            'label' => '<b>contact</b>',
            'form_value' => 'must never be stored',
        ])->assertAccepted();

        $event = VisitorEvent::firstOrFail();
        $this->assertSame('contact', $event->label);
        $this->assertSame(['locale' => 'ar'], $event->metadata);
        $this->assertStringNotContainsString('must never be stored', $event->toJson());
    }

    public function test_anonymous_analytics_config_does_not_render_a_popup_by_default(): void
    {
        $this->view('site.partials.consent')
            ->assertSee('tracking-config', false)
            ->assertDontSee('tracking-consent', false);
    }

    public function test_bots_are_ignored(): void
    {
        $this->withHeader('User-Agent', 'Googlebot')->postJson(route('analytics.collect'), $this->base(['type' => 'pageview']))
            ->assertOk()->assertJson(['accepted' => false]);
        $this->assertDatabaseCount('visitor_sessions', 0);
    }

    public function test_dashboard_page_and_all_visitor_widgets_render(): void
    {
        Permission::findOrCreate('dashboard.view', 'web');
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo('dashboard.view');
        $this->collect(['type' => 'pageview', 'path' => '/ar/work'])->assertAccepted();

        $this->actingAs($user)->get(VisitorAnalytics::getUrl())
            ->assertOk()->assertSee('تحليلات الزوار');

        foreach ([VisitorOverview::class, VisitorTrendChart::class, VisitorSourcesChart::class, TopPagesTable::class, RecentVisitorsTable::class] as $widget) {
            Livewire::actingAs($user)->test($widget)->assertOk();
        }
    }

    private function collect(array $values)
    {
        return $this->withHeader('User-Agent', 'Mozilla/5.0 Chrome/130.0 Windows')
            ->postJson(route('analytics.collect'), $this->base($values));
    }

    private function base(array $values): array
    {
        return array_merge([
            'visitor_id' => $this->visitorId,
            'session_id' => $this->sessionId,
            'pageview_id' => $this->pageviewId,
            'path' => '/ar',
            'title' => 'Home',
            'locale' => 'ar',
            'language' => 'ar-EG',
        ], $values);
    }
}


