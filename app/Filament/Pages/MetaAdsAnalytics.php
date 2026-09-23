<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\MetaAdPerformanceTable;
use App\Filament\Widgets\MetaAdsDecisionOverview;
use App\Filament\Widgets\MetaAdsEfficiencyChart;
use App\Filament\Widgets\MetaAdsOverview;
use App\Filament\Widgets\MetaAdsTrendChart;
use App\Filament\Widgets\MetaCampaignPerformanceTable;
use App\Support\MetaAds;
use App\Support\Studio;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Throwable;

class MetaAdsAnalytics extends Dashboard
{
    use HasFiltersForm;

    protected static string $routePath = 'meta-ads';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->can('leads.view');
    }

    public static function getNavigationLabel(): string
    {
        return Studio::text('meta_analytics');
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return Studio::text('clients');
    }

    public function getTitle(): string
    {
        return Studio::text('meta_analytics');
    }

    public function getSubheading(): ?string
    {
        return Studio::text('meta_analytics_description');
    }

    public function getColumns(): int|array
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [
            MetaAdsOverview::class,
            MetaAdsDecisionOverview::class,
            MetaAdsTrendChart::class,
            MetaAdsEfficiencyChart::class,
            MetaCampaignPerformanceTable::class,
            MetaAdPerformanceTable::class,
        ];
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(Studio::text('analysis_period'))
                ->description(Studio::text('analysis_period_help'))
                ->schema([
                    DatePicker::make('startDate')->label(Studio::text('from'))->default(today()->subDays(29))->maxDate(today())->native(false),
                    DatePicker::make('endDate')->label(Studio::text('until'))->default(today())->maxDate(today())->afterOrEqual('startDate')->native(false),
                ])->columns(2)->compact(),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncMetaAds')
                ->label(Studio::text('meta_ads_sync_now'))
                ->icon('heroicon-o-arrow-path')
                ->disabled(fn (): bool => ! app(MetaAds::class)->configured())
                ->tooltip(fn (): ?string => app(MetaAds::class)->configured() ? null : Studio::text('meta_ads_not_configured'))
                ->action(function (): void {
                    $meta = app(MetaAds::class);
                    $insights = null;
                    $leads = null;
                    $errors = [];

                    try {
                        $insights = $meta->syncInsights(90);
                    } catch (Throwable $error) {
                        report($error);
                        $errors[] = Studio::text('meta_ads_insights_sync_failed').': '.$meta->readableError($error);
                    }

                    if ($meta->leadsConfigured()) {
                        try {
                            $leads = $meta->syncLeads(90);
                        } catch (Throwable $error) {
                            report($error);
                            $errors[] = Studio::text('meta_ads_leads_sync_failed').': '.$meta->readableError($error);
                        }
                    } else {
                        $leads = 0;
                    }

                    $summary = Studio::text('meta_ads_sync_summary', [
                        'insights' => $insights ?? 0,
                        'leads' => $leads ?? 0,
                    ]);

                    if ($errors === []) {

                        Notification::make()
                            ->success()
                            ->title(Studio::text('meta_ads_sync_complete'))
                            ->body($summary)
                            ->send();
                    } elseif ($insights !== null || $leads !== null) {
                        Notification::make()
                            ->warning()
                            ->title(Studio::text('meta_ads_sync_partial'))
                            ->body($summary."\n\n".implode("\n", $errors))
                            ->persistent()
                            ->send();
                    } else {
                        Notification::make()
                            ->danger()
                            ->title(Studio::text('meta_ads_sync_failed'))
                            ->body(implode("\n", $errors))
                            ->persistent()
                            ->send();
                    }

                    $this->redirect(static::getUrl());
                }),
        ];
    }
}
