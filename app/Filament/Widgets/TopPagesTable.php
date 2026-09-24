<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasVisitorDateRange;
use App\Models\VisitorPageView;
use App\Support\Studio;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class TopPagesTable extends TableWidget
{
    use HasVisitorDateRange;

    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('dashboard.view');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(Studio::text('top_pages'))
            ->query(fn (): Builder => $this->visitorsInPeriod(VisitorPageView::query(), 'viewed_at')
                ->selectRaw('MIN(id) as id, path, COUNT(*) as views, COUNT(DISTINCT visitor_session_id) as sessions, ROUND(AVG(duration_seconds)) as avg_duration, ROUND(AVG(scroll_depth)) as avg_scroll')
                ->groupBy('path'))
            ->columns([
                TextColumn::make('path')->label(Studio::text('page'))->searchable()->wrap(),
                TextColumn::make('views')->label(Studio::text('page_views'))->numeric()->sortable(),
                TextColumn::make('sessions')->label(Studio::text('visitor_sessions'))->numeric()->sortable(),
                TextColumn::make('avg_duration')->label(Studio::text('avg_duration'))->formatStateUsing(fn ($state) => gmdate('i:s', (int) $state))->sortable(),
                TextColumn::make('avg_scroll')->label(Studio::text('scroll_depth'))->suffix('%')->sortable(),
            ])
            ->defaultSort('views', 'desc')->defaultKeySort(false)->paginated([5, 10, 25]);
    }
}
