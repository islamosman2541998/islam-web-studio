<?php

namespace App\Filament\Resources\Leads\RelationManagers;

use App\Support\Studio;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/** Read-only history of every WhatsApp and email message sent to a lead. */
class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return (bool) auth()->user()?->can('leads.view');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return Studio::text('message_history');
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        $statusColors = ['pending' => 'warning', 'sent' => 'success', 'failed' => 'danger'];

        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user'))
            ->columns([
                TextColumn::make('channel')->label(Studio::text('message_channel'))->badge()
                    ->formatStateUsing(fn (string $state): string => Studio::text('channel_'.$state))
                    ->color(fn (string $state): string => $state === 'whatsapp' ? 'success' : 'info'),
                TextColumn::make('status')->label(Studio::text('field_status'))->badge()
                    ->formatStateUsing(fn (string $state): string => Studio::text('message_status_'.$state))
                    ->color(fn (string $state): string => $statusColors[$state] ?? 'gray')
                    ->tooltip(fn ($record): ?string => $record->error),
                TextColumn::make('subject')->label(Studio::text('message_subject'))->placeholder('—')->toggleable(),
                TextColumn::make('body')->label(Studio::text('field_message'))->limit(120)->wrap(),
                TextColumn::make('user.name')->label(Studio::text('prepared_by'))->placeholder('—'),
                TextColumn::make('sent_at')->label(Studio::text('sent_at'))->dateTime('Y-m-d H:i')->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25])
            ->emptyStateHeading(Studio::text('no_messages_yet'));
    }
}
