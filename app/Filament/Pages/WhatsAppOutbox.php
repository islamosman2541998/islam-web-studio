<?php

namespace App\Filament\Pages;

use App\Models\LeadMessage;
use App\Support\LeadMessaging;
use App\Support\Studio;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/** Prepared WhatsApp messages wait here until the team opens each chat; opening one marks it sent. */
class WhatsAppOutbox extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.whatsapp-outbox';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-oval-left';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'whatsapp-outbox';

    public static function canAccess(): bool
    {
        return (bool) (auth()->user()?->is_active && auth()->user()->can('leads.update'));
    }

    public static function getNavigationLabel(): string
    {
        return Studio::text('whatsapp_outbox');
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return Studio::text('clients');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = self::pending()->count();

        return $count > 0 ? (string) $count : null;
    }

    public function getTitle(): string
    {
        return self::getNavigationLabel();
    }

    public function getSubheading(): ?string
    {
        return Studio::text('whatsapp_outbox_help');
    }

    public function table(Table $table): Table
    {
        $messaging = app(LeadMessaging::class);

        return $table
            ->query(self::pending()->with(['lead', 'user']))
            ->columns([
                TextColumn::make('lead.name')->label(Studio::text('field_name'))->weight('semibold')->searchable(),
                TextColumn::make('recipient')->label(Studio::text('field_phone'))->copyable(),
                TextColumn::make('body')->label(Studio::text('field_message'))->limit(90)->wrap(),
                TextColumn::make('user.name')->label(Studio::text('prepared_by'))->toggleable(),
                TextColumn::make('created_at')->label(Studio::text('prepared_at'))->since()->sortable(),
            ])
            ->recordActions([
                Action::make('open_whatsapp')->label(Studio::text('open_whatsapp'))->icon('heroicon-o-paper-airplane')->color('success')->button()
                    ->alpineClickHandler(fn (LeadMessage $record): string => "window.open('".$messaging->whatsAppUrl($record)."', '_blank', 'noopener'); \$wire.markSent(".$record->id.')'),
                Action::make('discard')->label(Studio::text('discard_message'))->icon('heroicon-o-x-mark')->color('danger')->requiresConfirmation()
                    ->action(fn (LeadMessage $record) => $record->delete()),
            ])
            ->toolbarActions([
                BulkAction::make('discard_selected')->label(Studio::text('discard_message'))->icon('heroicon-o-x-mark')->color('danger')->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->delete())->deselectRecordsAfterCompletion(),
            ])
            ->defaultSort('created_at')->paginated([25, 50, 100])
            ->emptyStateHeading(Studio::text('whatsapp_outbox_empty'));
    }

    public function markSent(int $id): void
    {
        abort_unless(self::canAccess(), 403);
        app(LeadMessaging::class)->markSent(self::pending()->findOrFail($id));
        Notification::make()->success()->title(Studio::text('message_marked_sent'))->send();
    }

    /** @return Builder<LeadMessage> */
    private static function pending(): Builder
    {
        return LeadMessage::query()->where('channel', 'whatsapp')->where('status', 'pending');
    }
}
