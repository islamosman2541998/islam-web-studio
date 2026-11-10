<?php

namespace Tests\Feature;

use App\Filament\Pages\WhatsAppOutbox;
use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Leads\Pages\EditRecord;
use App\Filament\Resources\Leads\Pages\ListRecords;
use App\Filament\Resources\Leads\RelationManagers\MessagesRelationManager;
use App\Filament\Resources\MetaLeads\Pages\ListMetaLeads;
use App\Mail\LeadMessageMail;
use App\Models\Lead;
use App\Models\LeadMessage;
use App\Models\Service;
use App\Models\User;
use App\Support\LeadMessaging;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class LeadMessagingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function userWith(array $permissions): User
    {
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo($permissions);

        return $user;
    }

    public function test_templates_are_personalised_per_lead_and_phones_are_normalised_for_whatsapp(): void
    {
        $messaging = app(LeadMessaging::class);
        $service = Service::factory()->create(['name' => ['ar' => 'تطوير المواقع', 'en' => 'Web development']]);
        $arabicLead = Lead::factory()->create(['name' => 'محمد أحمد', 'service_id' => $service->id, 'locale' => 'ar']);
        $englishLead = Lead::factory()->create(['name' => 'Sara Ali', 'service_id' => $service->id, 'locale' => 'en']);

        $this->assertSame('أهلًا محمد (محمد أحمد) بخصوص تطوير المواقع', $messaging->personalize('أهلًا {الاسم_الأول} ({الاسم}) بخصوص {الخدمة}', $arabicLead));
        $this->assertSame('Hi Sara, about Web development', $messaging->personalize('Hi {first_name}, about {service}', $englishLead));

        $this->assertSame('201012345678', $messaging->whatsAppNumber('010 1234 5678'));
        $this->assertSame('201012345678', $messaging->whatsAppNumber('+20 101 234 5678'));
        $this->assertSame('201012345678', $messaging->whatsAppNumber('00201012345678'));
        $this->assertSame('966501234567', $messaging->whatsAppNumber('+966 50 123 4567'));
        $this->assertNull($messaging->whatsAppNumber('123'));
        $this->assertNull($messaging->whatsAppNumber(null));
    }

    public function test_email_messages_are_personalised_editable_sent_and_logged(): void
    {
        Mail::fake();
        $user = $this->userWith(['leads.view', 'leads.update']);
        $first = Lead::factory()->create(['name' => 'Omar Hassan', 'email' => 'omar@example.test', 'status' => 'new']);
        $second = Lead::factory()->create(['name' => 'Laila Samir', 'email' => 'laila@example.test', 'status' => 'quoted']);
        $withoutEmail = Lead::factory()->create(['name' => 'No Email', 'email' => null, 'status' => 'new']);

        $component = Livewire::actingAs($user)->test(ListRecords::class)
            ->mountTableBulkAction('send_message', [$first, $second, $withoutEmail])
            ->fillForm(['channel' => 'email', 'subject' => 'Your project', 'template' => 'Hello {first_name}']);

        $messages = collect($component->instance()->mountedActions[0]['data']['messages']);
        $this->assertEquals([$first->id => 'Hello Omar', $second->id => 'Hello Laila', $withoutEmail->id => 'Hello No'], $messages->pluck('body', 'lead_id')->sortKeys()->all());

        $secondKey = $messages->search(fn (array $item): bool => (int) $item['lead_id'] === $second->id);
        $component->set('mountedActions.0.data.messages.'.$secondKey.'.body', 'A custom note for Laila')
            ->callMountedAction()
            ->assertHasNoFormErrors();

        Mail::assertSent(LeadMessageMail::class, fn (LeadMessageMail $mail) => $mail->hasTo('omar@example.test') && $mail->leadMessage->body === 'Hello Omar' && $mail->hasSubject('Your project'));
        Mail::assertSent(LeadMessageMail::class, fn (LeadMessageMail $mail) => $mail->hasTo('laila@example.test') && $mail->leadMessage->body === 'A custom note for Laila');
        Mail::assertSentCount(2);

        $this->assertDatabaseHas('lead_messages', ['lead_id' => $first->id, 'channel' => 'email', 'status' => 'sent', 'user_id' => $user->id]);
        $this->assertDatabaseMissing('lead_messages', ['lead_id' => $withoutEmail->id]);
        $this->assertSame('contacted', $first->fresh()->status);
        $this->assertSame('quoted', $second->fresh()->status);
    }

    public function test_whatsapp_messages_wait_in_the_outbox_until_opened(): void
    {
        $user = $this->userWith(['dashboard.view', 'leads.view', 'leads.update']);
        $lead = Lead::factory()->create(['name' => 'Karim Adel', 'phone' => '01012345678', 'status' => 'new']);
        $withoutPhone = Lead::factory()->create(['name' => 'No Phone', 'phone' => null]);

        Livewire::actingAs($user)->test(ListRecords::class)
            ->callTableBulkAction('send_message', [$lead, $withoutPhone], data: ['channel' => 'whatsapp', 'template' => 'أهلًا {الاسم}'])
            ->assertHasNoFormErrors()
            ->assertRedirect(WhatsAppOutbox::getUrl());

        $message = LeadMessage::sole();
        $this->assertSame(['whatsapp', 'pending', '201012345678', 'أهلًا Karim Adel'], [$message->channel, $message->status, $message->recipient, $message->body]);
        $this->assertSame('new', $lead->fresh()->status);
        $this->actingAs($user)->get(WhatsAppOutbox::getUrl())->assertOk()->assertSee('Karim Adel');
        $this->actingAs($user)->get(LeadResource::getUrl('edit', ['record' => $lead]))->assertOk();

        Livewire::actingAs($user)->test(WhatsAppOutbox::class)
            ->assertCanSeeTableRecords([$message])
            ->assertSeeHtml("window.open('https://wa.me/201012345678?text=".rawurlencode('أهلًا Karim Adel')."', '_blank', 'noopener'); \$wire.markSent(".$message->id.')')
            ->call('markSent', $message->id)
            ->assertCanNotSeeTableRecords([$message]);

        $this->assertSame('sent', $message->fresh()->status);
        $this->assertNotNull($message->fresh()->sent_at);
        $this->assertSame('contacted', $lead->fresh()->status);
    }

    public function test_messaging_requires_update_permission_and_history_is_shown_on_the_lead(): void
    {
        $viewer = $this->userWith(['dashboard.view', 'leads.view']);
        $lead = Lead::factory()->create(['name' => 'Mona', 'phone' => '01012345678', 'source' => 'meta']);
        $sent = LeadMessage::factory()->sent()->create(['lead_id' => $lead->id, 'body' => 'Earlier follow-up']);

        Livewire::actingAs($viewer)->test(ListMetaLeads::class)->assertTableBulkActionHidden('send_message');
        $this->actingAs($viewer)->get(WhatsAppOutbox::getUrl())->assertForbidden();

        $siteLead = Lead::factory()->create(['name' => 'Site lead']);
        LeadMessage::factory()->sent()->create(['lead_id' => $siteLead->id, 'body' => 'Site follow-up']);
        Livewire::actingAs($viewer)->test(MessagesRelationManager::class, ['ownerRecord' => $siteLead, 'pageClass' => EditRecord::class])
            ->assertOk()
            ->assertSee('Site follow-up')
            ->assertDontSee($sent->body);
    }
}
