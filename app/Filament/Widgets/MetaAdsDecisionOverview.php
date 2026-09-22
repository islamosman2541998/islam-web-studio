<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasMetaDateRange;
use App\Models\MetaAdInsight;
use App\Support\Studio;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MetaAdsDecisionOverview extends StatsOverviewWidget
{
    use HasMetaDateRange;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '5m';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('leads.view');
    }

    protected function getHeading(): ?string
    {
        return Studio::text('meta_decision_metrics');
    }

    protected function getDescription(): ?string
    {
        return Studio::text('meta_decision_metrics_description');
    }

    protected function getStats(): array
    {
        $query = $this->inPeriod(MetaAdInsight::query());
        $spend = (float) (clone $query)->sum('spend');
        $impressions = (int) (clone $query)->sum('impressions');
        $reach = (int) (clone $query)->sum('reach');
        $clicks = (int) (clone $query)->sum('clicks');
        $linkClicks = (int) (clone $query)->sum('link_clicks');
        $leads = (int) (clone $query)->sum('leads');
        $currency = MetaAdInsight::query()->latest('date')->value('currency') ?: 'EGP';

        return [
            Stat::make(Studio::text('meta_ctr'), $impressions > 0 ? number_format($clicks / $impressions * 100, 2).'%' : '—')
                ->description(Studio::text('meta_ctr_help'))->icon('heroicon-o-cursor-arrow-rays')->color('info'),
            Stat::make(Studio::text('meta_link_ctr'), $impressions > 0 ? number_format($linkClicks / $impressions * 100, 2).'%' : '—')
                ->description(Studio::text('meta_link_ctr_help'))->icon('heroicon-o-link')->color('primary'),
            Stat::make(Studio::text('meta_cpc'), $clicks > 0 ? number_format($spend / $clicks, 2).' '.$currency : '—')
                ->description(Studio::text('meta_cpc_help'))->icon('heroicon-o-cursor-arrow-ripple')->color('warning'),
            Stat::make(Studio::text('meta_cpm'), $impressions > 0 ? number_format($spend / $impressions * 1000, 2).' '.$currency : '—')
                ->description(Studio::text('meta_cpm_help'))->icon('heroicon-o-eye')->color('gray'),
            Stat::make(Studio::text('meta_click_to_lead'), $clicks > 0 ? number_format($leads / $clicks * 100, 2).'%' : '—')
                ->description(Studio::text('meta_click_to_lead_help'))->icon('heroicon-o-arrow-trending-up')->color('success'),
            Stat::make(Studio::text('meta_frequency'), $reach > 0 ? number_format($impressions / $reach, 2) : '—')
                ->description(Studio::text('meta_frequency_help'))->icon('heroicon-o-arrow-path-rounded-square')->color('gray'),
        ];
    }
}
