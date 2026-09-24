<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\RecentVisitorsTable;
use App\Filament\Widgets\TopPagesTable;
use App\Filament\Widgets\VisitorOverview;
use App\Filament\Widgets\VisitorSourcesChart;
use App\Filament\Widgets\VisitorTrendChart;
use App\Support\Studio;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VisitorAnalytics extends Dashboard
{
    use HasFiltersForm;

    protected static string $routePath = 'visitor-analytics';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('dashboard.view');
    }

    public static function getNavigationLabel(): string
    {
        return Studio::text('visitor_analytics');
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return Studio::text('clients');
    }

    public function getTitle(): string
    {
        return Studio::text('visitor_analytics');
    }

    public function getSubheading(): ?string
    {
        return Studio::text('visitor_analytics_description');
    }

    public function getColumns(): int|array
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [
            VisitorOverview::class,
            VisitorTrendChart::class,
            VisitorSourcesChart::class,
            TopPagesTable::class,
            RecentVisitorsTable::class,
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(Studio::text('analysis_period'))
                ->description(Studio::text('analysis_period_help'))
                ->schema([
                    DatePicker::make('startDate')->label(Studio::text('from'))->default(today()->subDays(29))->maxDate(today())->native(false),
                    DatePicker::make('endDate')->label(Studio::text('until'))->default(today())->maxDate(today())->afterOrEqual('startDate')->native(false),
                ])->columns(2)->compact(),
        ]);
    }
}
