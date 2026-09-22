<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasMetaDateRange;
use App\Models\MetaAdInsight;
use App\Support\Studio;
use Filament\Widgets\ChartWidget;

class MetaAdsEfficiencyChart extends ChartWidget
{
    use HasMetaDateRange;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '330px';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('leads.view');
    }

    public function getHeading(): string
    {
        return Studio::text('meta_efficiency_trend');
    }

    public function getDescription(): string
    {
        return Studio::text('meta_efficiency_trend_description');
    }

    protected function getData(): array
    {
        $days = collect(range(0, (int) min(89, $this->startDate()->diffInDays($this->endDate()))))
            ->map(fn (int $offset) => $this->startDate()->copy()->addDays($offset));
        $rows = $this->inPeriod(MetaAdInsight::query())
            ->selectRaw('date, SUM(spend) as spend, SUM(leads) as leads, SUM(clicks) as clicks, SUM(impressions) as impressions')
            ->groupBy('date')->get()->keyBy(fn (MetaAdInsight $row) => $row->date->toDateString());

        return [
            'datasets' => [
                [
                    'label' => Studio::text('meta_ads_cost_per_lead'),
                    'data' => $days->map(function ($day) use ($rows): float {
                        $row = $rows->get($day->toDateString());

                        return $row && (int) $row->leads > 0 ? round((float) $row->spend / (int) $row->leads, 2) : 0;
                    })->all(),
                    'borderColor' => Studio::color(Studio::setting('dashboard.primary'), '#105666'),
                    'backgroundColor' => Studio::rgba(Studio::setting('dashboard.primary'), 14, '#105666'),
                    'fill' => true, 'tension' => 0.35, 'yAxisID' => 'y',
                ],
                [
                    'label' => Studio::text('meta_ctr'),
                    'data' => $days->map(function ($day) use ($rows): float {
                        $row = $rows->get($day->toDateString());

                        return $row && (int) $row->impressions > 0 ? round((int) $row->clicks / (int) $row->impressions * 100, 2) : 0;
                    })->all(),
                    'borderColor' => Studio::color(Studio::setting('design.warm'), '#D3968C'),
                    'backgroundColor' => Studio::color(Studio::setting('design.warm'), '#D3968C'),
                    'tension' => 0.35, 'yAxisID' => 'y1',
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
                'y1' => ['beginAtZero' => true, 'position' => 'right', 'grid' => ['drawOnChartArea' => false]],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
