<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasVisitorDateRange;
use App\Models\VisitorSession;
use App\Support\Studio;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentVisitorsTable extends TableWidget
{
    use HasVisitorDateRange;

    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('dashboard.view');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(Studio::text('recent_visitors'))
            ->description(Studio::text('recent_visitors_description'))
            ->query(fn (): Builder => $this->visitorsInPeriod(VisitorSession::query())->latest('last_seen_at'))
            ->columns([
                TextColumn::make('last_seen_at')->label(Studio::text('last_seen'))->since()->dateTimeTooltip()->sortable(),
                TextColumn::make('source')->label(Studio::text('source'))->state(fn (VisitorSession $record) => $record->utm_source ?: $record->referrer_host ?: Studio::text('direct'))->searchable(['utm_source', 'referrer_host']),
                TextColumn::make('landing_path')->label(Studio::text('landing_page'))->wrap()->limit(45)->searchable(),
                TextColumn::make('device_type')->label(Studio::text('device'))->formatStateUsing(fn ($state) => Studio::text('device_'.$state))->badge(),
                TextColumn::make('browser')->label(Studio::text('browser'))->toggleable(),
                TextColumn::make('country_code')->label(Studio::text('country'))->placeholder('—')->toggleable(),
                TextColumn::make('page_views_count')->label(Studio::text('pages'))->numeric()->sortable(),
                TextColumn::make('duration_seconds')->label(Studio::text('duration'))->formatStateUsing(fn ($state) => gmdate('i:s', (int) $state))->sortable(),
            ])
            ->recordActions([
                Action::make('journey')
                    ->label(Studio::text('view_journey'))
                    ->icon('heroicon-o-map')
                    ->modalHeading(Studio::text('visitor_journey'))
                    ->modalContent(fn (VisitorSession $record) => view('filament.widgets.visitor-journey', ['session' => $record->load(['pageViews.events'])]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(Studio::text('close')),
            ])
            ->defaultSort('last_seen_at', 'desc')->paginated([10, 25, 50]);
    }
}
