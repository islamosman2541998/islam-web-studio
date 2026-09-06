<?php

namespace App\Filament\Widgets;

use App\Support\Studio;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StudioStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $stats = [];
        foreach (['services' => 'Service', 'projects' => 'Project', 'posts' => 'Post', 'leads' => 'Lead'] as $module => $model) {
            if (auth()->user()?->can($module.'.view')) {
                $class = 'App\\Models\\'.$model;
                $stats[] = Stat::make(Studio::text($module), $class::count())->description($module === 'leads' ? Studio::text('new').': '.$class::where('status', 'new')->count() : Studio::text('published').': '.$class::published()->count())->color('primary');
            }
        }

        return $stats;
    }
}
