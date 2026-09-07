<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Leads\LeadResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Lead;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Support\Studio;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class StudioStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '60s';

    protected function getHeading(): ?string
    {
        return Studio::text('dashboard_key_metrics');
    }

    protected function getDescription(): ?string
    {
        return Studio::text('dashboard_key_metrics_description');
    }

    protected function getStats(): array
    {
        $stats = [];

        foreach ($this->contentModules() as $module => $definition) {
            if (! auth()->user()?->can($module.'.view')) {
                continue;
            }

            $model = $definition['model'];
            $published = $model::query()->where('status', 'published')->where('is_active', true)->count();
            $stats[] = Stat::make(Studio::text($module), $model::count())
                ->description(Studio::text('dashboard_published_now', ['count' => $published]))
                ->descriptionIcon('heroicon-m-check-circle')
                ->icon($definition['icon'])
                ->chart($this->dailyCounts($model))
                ->chartColor($definition['color'])
                ->color($definition['color'])
                ->url($definition['resource']::getUrl('index'))
                ->extraAttributes(['class' => 'studio-dashboard-stat']);
        }

        if (auth()->user()?->can('leads.view')) {
            $new = Lead::query()->where('status', 'new')->count();
            $stats[] = Stat::make(Studio::text('leads'), Lead::count())
                ->description(Studio::text('dashboard_new_waiting', ['count' => $new]))
                ->descriptionIcon($new > 0 ? 'heroicon-m-bell-alert' : 'heroicon-m-check-circle')
                ->icon('heroicon-o-inbox-arrow-down')
                ->chart($this->dailyCounts(Lead::class))
                ->chartColor('warning')
                ->color('warning')
                ->url(LeadResource::getUrl('index'))
                ->extraAttributes(['class' => 'studio-dashboard-stat']);
        }

        return $stats;
    }

    /**
     * @return array<string, array{model: class-string<Model>, resource: class-string, icon: string, color: string}>
     */
    private function contentModules(): array
    {
        return [
            'services' => ['model' => Service::class, 'resource' => ServiceResource::class, 'icon' => 'heroicon-o-wrench-screwdriver', 'color' => 'success'],
            'projects' => ['model' => Project::class, 'resource' => ProjectResource::class, 'icon' => 'heroicon-o-computer-desktop', 'color' => 'primary'],
            'posts' => ['model' => Post::class, 'resource' => PostResource::class, 'icon' => 'heroicon-o-newspaper', 'color' => 'info'],
        ];
    }

    /**
     * @param  class-string<Model>  $model
     * @return array<int, int>
     */
    private function dailyCounts(string $model): array
    {
        $start = today()->subDays(6);
        $counts = $model::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->countBy(fn (Model $record): string => $record->created_at->toDateString());

        return collect(range(0, 6))
            ->map(fn (int $day): int => (int) $counts->get($start->copy()->addDays($day)->toDateString(), 0))
            ->all();
    }
}
