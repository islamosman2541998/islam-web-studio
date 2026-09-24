<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasVisitorDateRange;
use App\Models\VisitorPageView;
use App\Models\VisitorSession;
use App\Support\Studio;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VisitorOverview extends StatsOverviewWidget
{
    use HasVisitorDateRange;

    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';
    protected ?string $pollingInterval = '1m';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('dashboard.view');
    }

    protected function getHeading(): ?string
    {
        return Studio::text('visitor_overview');
    }

    protected function getStats(): array
    {
        $sessions = $this->visitorsInPeriod(VisitorSession::query());
        $sessionCount = (clone $sessions)->count();
        $unique = (clone $sessions)->distinct()->count('visitor_hash');
        $views = $this->visitorsInPeriod(VisitorPageView::query(), 'viewed_at')->count();
        $average = $sessionCount ? (int) round((clone $sessions)->avg('duration_seconds')) : 0;
        $bounce = $sessionCount ? (clone $sessions)->where('page_views_count', '<=', 1)->count() / $sessionCount * 100 : 0;

        return [
            Stat::make(Studio::text('unique_visitors'), number_format($unique))->icon('heroicon-o-users')->color('primary'),
            Stat::make(Studio::text('visitor_sessions'), number_format($sessionCount))->icon('heroicon-o-arrow-path-rounded-square')->color('info'),
            Stat::make(Studio::text('page_views'), number_format($views))->icon('heroicon-o-eye')->color('success'),
            Stat::make(Studio::text('avg_session_duration'), $this->duration($average))->description(Studio::text('bounce_rate').': '.number_format($bounce, 1).'%')->icon('heroicon-o-clock')->color('warning'),
        ];
    }

    private function duration(int $seconds): string
    {
        return sprintf('%02d:%02d', intdiv($seconds, 60), $seconds % 60);
    }
}
