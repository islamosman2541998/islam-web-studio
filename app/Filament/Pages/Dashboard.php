<?php

namespace App\Filament\Pages;

use App\Jobs\SyncMetaAdsInsights;
use App\Support\MetaAds;
use App\Support\Studio;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Throwable;

class Dashboard extends \Filament\Pages\Dashboard
{
    public function getTitle(): string
    {
        return Studio::text('dashboard_title');
    }

    public static function getNavigationLabel(): string
    {
        return Studio::text('overview');
    }

    public function getSubheading(): ?string
    {
        return Studio::text('dashboard_subheading');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncMetaAds')
                ->label(Studio::text('meta_ads_sync_now'))
                ->icon('heroicon-o-arrow-path')
                ->visible(fn (): bool => (bool) auth()->user()?->can('leads.view'))
                ->disabled(fn (): bool => ! app(MetaAds::class)->configured())
                ->tooltip(fn (): ?string => app(MetaAds::class)->configured() ? null : Studio::text('meta_ads_not_configured'))
                ->action(function (): void {
                    try {
                        SyncMetaAdsInsights::dispatchSync(30);
                        Notification::make()->success()->title(Studio::text('meta_ads_sync_complete'))->send();
                    } catch (Throwable $error) {
                        report($error);
                        Notification::make()->danger()->title(Studio::text('meta_ads_sync_failed'))->body($error->getMessage())->persistent()->send();
                    }
                }),
        ];
    }
}
