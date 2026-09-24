<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasVisitorDateRange;
use App\Models\VisitorSession;
use App\Support\Studio;
use Filament\Widgets\ChartWidget;

class VisitorSourcesChart extends ChartWidget
{
    use HasVisitorDateRange;

    protected static ?int $sort = 3;
    protected int|string|array $columnSpan = 1;
    protected ?string $maxHeight = '320px';
    protected ?string $pollingInterval = '1m';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('dashboard.view');
    }

    public function getHeading(): string
    {
        return Studio::text('visitor_sources');
    }

    protected function getData(): array
    {
        $rows = $this->visitorsInPeriod(VisitorSession::query())->get(['utm_source', 'referrer_host'])
            ->groupBy(fn (VisitorSession $session) => $session->utm_source ?: $session->referrer_host ?: Studio::text('direct'))
            ->map->count()->sortDesc()->take(8);

        return ['datasets' => [[
            'data' => $rows->values()->all(),
            'backgroundColor' => ['#105666', '#D3968C', '#839958', '#E1C468', '#4F7D70', '#C17C74', '#7699B7', '#B2A48C'],
            'borderWidth' => 0,
        ]], 'labels' => $rows->keys()->all()];
    }

    protected function getOptions(): array
    {
        return ['maintainAspectRatio' => false, 'plugins' => ['legend' => ['position' => 'bottom']]];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
