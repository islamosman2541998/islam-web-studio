<?php

namespace App\Filament\Widgets;

use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Support\Studio;
use Filament\Widgets\ChartWidget;

class ContentStatusChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '300px';

    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        return collect(['services', 'projects', 'posts'])->contains(fn (string $module): bool => (bool) auth()->user()?->can($module.'.view'));
    }

    public function getHeading(): string
    {
        return Studio::text('dashboard_content_status');
    }

    public function getDescription(): string
    {
        return Studio::text('dashboard_content_status_description');
    }

    protected function getData(): array
    {
        $labels = [];
        $published = [];
        $unpublished = [];

        foreach (['services' => Service::class, 'projects' => Project::class, 'posts' => Post::class] as $module => $model) {
            if (! auth()->user()?->can($module.'.view')) {
                continue;
            }

            $labels[] = Studio::text($module);
            $visible = $model::query()->where('status', 'published')->where('is_active', true)->count();
            $published[] = $visible;
            $unpublished[] = max(0, $model::count() - $visible);
        }

        return [
            'datasets' => [
                [
                    'label' => Studio::text('published'),
                    'data' => $published,
                    'backgroundColor' => Studio::color(Studio::setting('dashboard.primary'), '#105666'),
                    'borderRadius' => 7,
                    'borderSkipped' => false,
                ],
                [
                    'label' => Studio::text('dashboard_unpublished'),
                    'data' => $unpublished,
                    'backgroundColor' => Studio::color(Studio::setting('design.warm'), '#D3968C'),
                    'borderRadius' => 7,
                    'borderSkipped' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'maintainAspectRatio' => false,
            'plugins' => ['legend' => ['position' => 'bottom', 'labels' => ['usePointStyle' => true, 'boxWidth' => 8]]],
            'scales' => [
                'x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0], 'border' => ['display' => false]],
                'y' => ['grid' => ['display' => false], 'border' => ['display' => false]],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
