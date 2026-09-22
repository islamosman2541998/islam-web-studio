<?php

namespace App\Filament\Widgets;

use App\Models\Lead;
use App\Models\MetaAdInsight;
use App\Support\MetaAds;
use App\Support\Studio;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class MetaAdsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '5m';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('leads.view');
    }

    protected function getHeading(): ?string
    {
        return Studio::text('meta_ads_overview');
    }

    protected function getDescription(): ?string
    {
        if (! app(MetaAds::class)->configured()) {
            return Studio::text('meta_ads_not_configured');
        }

        $updated = MetaAdInsight::query()->max('updated_at');

        return $updated
            ? Studio::text('meta_ads_last_sync', ['time' => Carbon::parse($updated)->diffForHumans()])
            : Studio::text('meta_ads_no_data');
    }

    protected function getStats(): array
    {
        $query = MetaAdInsight::query()->whereDate('date', '>=', today()->subDays(6));
        $spend = (float) (clone $query)->sum('spend');
        $reportedLeads = (int) (clone $query)->sum('leads');
        $receivedLeads = Lead::query()->where('source', 'meta')->where('created_at', '>=', today()->subDays(6))->count();
        $currency = MetaAdInsight::query()->latest('date')->value('currency') ?: 'EGP';

        return [
            Stat::make(Studio::text('meta_ads_spend_7d'), number_format($spend, 2).' '.$currency)
                ->icon('heroicon-o-banknotes')->color('warning'),
            Stat::make(Studio::text('meta_ads_reported_leads'), number_format($reportedLeads))
                ->description(Studio::text('meta_ads_received_leads', ['count' => $receivedLeads]))
                ->icon('heroicon-o-user-plus')->color('success'),
            Stat::make(Studio::text('meta_ads_cost_per_lead'), $reportedLeads > 0 ? number_format($spend / $reportedLeads, 2).' '.$currency : '—')
                ->icon('heroicon-o-calculator')->color('primary'),
            Stat::make(Studio::text('meta_ads_reach_7d'), number_format((int) (clone $query)->sum('reach')))
                ->description(Studio::text('meta_ads_clicks', ['count' => (int) (clone $query)->sum('clicks')]))
                ->icon('heroicon-o-megaphone')->color('info'),
        ];
    }
}
