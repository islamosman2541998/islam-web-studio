<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasVisitorDateRange;
use App\Models\VisitorSession;
use App\Support\Studio;
use Filament\Widgets\ChartWidget;

class VisitorTrendChart extends ChartWidget
{
    use HasVisitorDateRange;

    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 1;
    protected ?string $maxHeight = '320px';
    protected ?string $pollingInterval = '1m';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('dashboard.view');
    }

    public function getHeading(): string
    {
        return Studio::text('visitor_trend');
    }

    protected function getData(): array
    {
        $days = collect(range(0, (int) min(89, $this->startDate()->diffInDays($this->endDate()))))
            ->map(fn (int $offset) => $this->startDate()->copy()->addDays($offset));
        $dateExpression = VisitorSession::query()->getConnection()->getDriverName() === 'sqlite'
            ? 'date(first_seen_at)'
            : 'DATE(first_seen_at)';
        $rows = $this->visitorsInPeriod(VisitorSession::query())
            ->selectRaw($dateExpression.' as day, COUNT(*) as sessions, COUNT(DISTINCT visitor_hash) as visitors')
            ->groupBy('day')->get()->keyBy('day');

        return [
            'datasets' => [
                ['label' => Studio::text('visitor_sessions'), 'data' => $days->map(fn ($day) => (int) ($rows->get($day->toDateString())?->sessions ?? 0))->all(), 'backgroundColor' => Studio::rgba(Studio::setting('design.warm'), 65, '#D3968C'), 'borderRadius' => 6],
                ['type' => 'line', 'label' => Studio::text('unique_visitors'), 'data' => $days->map(fn ($day) => (int) ($rows->get($day->toDateString())?->visitors ?? 0))->all(), 'borderColor' => Studio::color(Studio::setting('dashboard.primary'), '#105666'), 'backgroundColor' => Studio::color(Studio::setting('dashboard.primary'), '#105666'), 'borderWidth' => 2, 'tension' => 0.35],
            ],
            'labels' => $days->map(fn ($day) => $day->locale(app()->getLocale())->translatedFormat('j M'))->all(),
        ];
    }

    protected function getOptions(): array
    {
        return ['maintainAspectRatio' => false, 'interaction' => ['intersect' => false, 'mode' => 'index'], 'plugins' => ['legend' => ['position' => 'bottom']], 'scales' => ['x' => ['grid' => ['display' => false]], 'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]]];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
