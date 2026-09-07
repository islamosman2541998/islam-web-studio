<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Support\Studio;
use Filament\Widgets\ChartWidget;

class LeadActivityChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('leads.view');
    }

    public function getHeading(): string
    {
        return Studio::text('dashboard_lead_activity');
    }

    public function getDescription(): string
    {
        return Studio::text('dashboard_lead_activity_description');
    }

    protected function getData(): array
    {
        $days = collect(range(13, 0))->map(fn (int $offset) => today()->subDays($offset));
        $counts = Lead::query()
            ->where('created_at', '>=', $days->first()->copy()->startOfDay())
            ->get(['created_at'])
            ->countBy(fn (Lead $lead): string => $lead->created_at->toDateString());
        $primary = Studio::color(Studio::setting('dashboard.primary'), '#105666');

        return [
            'datasets' => [[
                'label' => Studio::text('leads'),
                'data' => $days->map(fn ($day): int => (int) $counts->get($day->toDateString(), 0))->all(),
                'borderColor' => $primary,
                'backgroundColor' => Studio::rgba($primary, 14, '#105666'),
                'borderWidth' => 2,
                'fill' => true,
                'tension' => 0.38,
                'pointRadius' => 0,
                'pointHoverRadius' => 4,
            ]],
            'labels' => $days->map(fn ($day): string => $day->locale(app()->getLocale())->translatedFormat('j M'))->all(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'interaction' => ['intersect' => false, 'mode' => 'index'],
            'plugins' => ['legend' => ['display' => false]],
            'scales' => [
                'x' => ['grid' => ['display' => false], 'border' => ['display' => false]],
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0], 'border' => ['display' => false]],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
