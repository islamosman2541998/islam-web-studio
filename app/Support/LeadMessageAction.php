<?php

namespace App\Support;

use App\Filament\Pages\WhatsAppOutbox;
use App\Models\Lead;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;

class LeadMessageAction
{
    /** Leads beyond this count would make the per-lead preview too heavy to edit in one modal. */
    public const MaxRecipients = 100;

    public static function bulk(): BulkAction
    {
        return BulkAction::make('send_message')
            ->label(Studio::text('send_message'))
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->visible(fn (): bool => ModuleRegistry::permission('leads', 'update'))
            ->modalHeading(Studio::text('send_message'))
            ->modalDescription(Studio::text('send_message_description'))
            ->modalWidth(Width::FourExtraLarge)
            ->modalSubmitActionLabel(Studio::text('send_message_submit'))
            ->fillForm(function (Collection $records): array {
                $template = Studio::text('send_message_default_template');

                return ['channel' => 'whatsapp', 'template' => $template, 'messages' => self::items($records->take(self::MaxRecipients), $template)];
            })
            ->schema([
                ToggleButtons::make('channel')->label(Studio::text('message_channel'))->inline()->required()->live()
                    ->options(['whatsapp' => Studio::text('channel_whatsapp'), 'email' => Studio::text('channel_email')])
                    ->icons(['whatsapp' => 'heroicon-o-chat-bubble-oval-left', 'email' => 'heroicon-o-envelope']),
                TextInput::make('subject')->label(Studio::text('message_subject'))->maxLength(150)
                    ->visible(fn (Get $get): bool => $get('channel') === 'email')
                    ->required(fn (Get $get): bool => $get('channel') === 'email'),
                Textarea::make('template')->label(Studio::text('message_template'))->rows(4)->maxLength(2000)
                    ->helperText(Studio::text('message_template_help'))
                    ->live(debounce: 600)
                    ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                        $items = (array) $get('messages');
                        $leads = Lead::with('service')->whereKey(array_column($items, 'lead_id'))->get()->keyBy('id');
                        $messaging = app(LeadMessaging::class);
                        foreach ($items as $key => $item) {
                            if ($lead = $leads->get((int) $item['lead_id'])) {
                                $items[$key]['body'] = $messaging->personalize((string) $state, $lead);
                            }
                        }
                        $set('messages', $items);
                    }),
                Repeater::make('messages')->label(Studio::text('message_previews'))
                    ->addable(false)->reorderable(false)->collapsible()
                    ->itemLabel(fn (array $state): string => (string) ($state['name'] ?? ''))
                    ->schema([
                        Hidden::make('lead_id'),
                        Hidden::make('name')->dehydrated(false),
                        Hidden::make('phone')->dehydrated(false),
                        Hidden::make('email')->dehydrated(false),
                        Textarea::make('body')->hiddenLabel()->rows(3)->maxLength(4000)
                            ->helperText(fn (Get $get): string => self::recipientHint($get('../../channel'), $get('phone'), $get('email'))),
                    ]),
            ])
            ->action(function (Collection $records, array $data, $livewire): void {
                abort_unless(ModuleRegistry::permission('leads', 'update') && in_array($data['channel'], ['whatsapp', 'email'], true), 403);
                $result = app(LeadMessaging::class)->prepare($records, $data['channel'], $data['messages'] ?? [], $data['subject'] ?? null, auth()->user());
                $body = $result['skipped'] > 0 ? Studio::text('messages_skipped', ['count' => $result['skipped']]) : null;
                if ($data['channel'] === 'email') {
                    Notification::make()->success()->title(Studio::text('emails_queued', ['count' => $result['prepared']]))->body($body)->send();

                    return;
                }
                Notification::make()->success()->title(Studio::text('whatsapp_prepared', ['count' => $result['prepared']]))->body($body)->send();
                if ($result['prepared'] > 0) {
                    $livewire->redirect(WhatsAppOutbox::getUrl(), navigate: true);
                }
            })
            ->deselectRecordsAfterCompletion();
    }

    /**
     * @param  Collection<int, Lead>  $leads
     * @return array<int, array{lead_id: int, name: string, phone: ?string, email: ?string, body: string}>
     */
    private static function items(Collection $leads, string $template): array
    {
        $leads->loadMissing('service');
        $messaging = app(LeadMessaging::class);

        return $leads->map(fn (Lead $lead): array => [
            'lead_id' => $lead->id,
            'name' => (string) $lead->name,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'body' => $messaging->personalize($template, $lead),
        ])->values()->all();
    }

    private static function recipientHint(?string $channel, ?string $phone, ?string $email): string
    {
        $recipient = $channel === 'email'
            ? (filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null)
            : app(LeadMessaging::class)->whatsAppNumber($phone);

        return $recipient ? Studio::text('message_recipient', ['recipient' => $recipient]) : Studio::text('message_no_recipient');
    }
}
