<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasMetaDateRange;
use App\Models\MetaAdInsight;
use App\Support\Studio;
use Filament\Widgets\ChartWidget;

class MetaAdsTrendChart extends ChartWidget
{
    use HasMetaDateRange;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '320px';

    protected ?string $pollingInterval = '5m';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('leads.view');
    }

    public function getHeading(): string
    {
        return Studio::text('meta_ads_trend');
    }

    protected function getData(): array
    {
        $days = collect(range(0, (int) min(89, $this->startDate()->diffInDays($this->endDate()))))
            ->map(fn (int $offset) => $this->startDate()->copy()->addDays($offset));
        $rows = $this->inPeriod(MetaAdInsight::query())
            ->selectRaw('date, SUM(spend) as spend, SUM(leads) as leads')
            ->groupBy('date')->get()->keyBy(fn (MetaAdInsight $row) => $row->date->toDateString());

        return [
            'datasets' => [
                [
                    'label' => Studio::text('meta_ads_spend'),
                    'data' => $days->map(fn ($day): float => (float) ($rows->get($day->toDateString())?->spend ?? 0))->all(),
                    'backgroundColor' => Studio::rgba(Studio::setting('design.warm'), 65, '#D3968C'),
                    'borderRadius' => 6,
                    'yAxisID' => 'y',
                ],
                [
                    'type' => 'line',
                    'label' => Studio::text('meta_ads_leads'),
                    'data' => $days->map(fn ($day): int => (int) ($rows->get($day->toDateString())?->leads ?? 0))->all(),
                    'borderColor' => Studio::color(Studio::setting('dashboard.primary'), '#105666'),
                    'backgroundColor' => Studio::color(Studio::setting('dashboard.primary'), '#105666'),
                    'borderWidth' => 2,
                    'tension' => 0.35,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $days->map(fn ($day): string => $day->locale(app()->getLocale())->translatedFormat('j M'))->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'interaction' => ['intersect' => false, 'mode' => 'index'],
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => [
                'x' => ['grid' => ['display' => false]],
                'y' => ['beginAtZero' => true, 'position' => 'left'],
                'y1' => ['beginAtZero' => true, 'position' => 'right', 'ticks' => ['precision' => 0], 'grid' => ['drawOnChartArea' => false]],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
