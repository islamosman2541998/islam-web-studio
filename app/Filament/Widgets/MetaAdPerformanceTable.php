<?php

namespace App\Filament\Widgets;

use App\Models\MetaAdInsight;
use App\Support\Studio;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MetaAdPerformanceTable extends TableWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('leads.view');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(Studio::text('meta_ads_per_ad'))
            ->description(Studio::text('meta_ads_per_ad_description'))
            ->query(fn (): Builder => MetaAdInsight::query()
                ->selectRaw('MIN(id) as id, campaign_name, ad_name, currency, SUM(spend) as spend, SUM(leads) as leads, SUM(reach) as reach, SUM(impressions) as impressions, SUM(clicks) as clicks')
                ->whereDate('date', '>=', today()->subDays(29))
                ->groupBy('campaign_name', 'ad_name', 'currency'))
            ->columns([
                TextColumn::make('campaign_name')->label(Studio::text('meta_campaign'))->searchable()->wrap(),
                TextColumn::make('ad_name')->label(Studio::text('meta_ad'))->searchable()->wrap(),
                TextColumn::make('spend')->label(Studio::text('meta_ads_spend'))->formatStateUsing(fn ($state, $record) => number_format((float) $state, 2).' '.$record->currency)->sortable(),
                TextColumn::make('leads')->label(Studio::text('meta_ads_leads'))->numeric()->sortable(),
                TextColumn::make('cost_per_lead')->label(Studio::text('meta_ads_cost_per_lead'))->state(fn ($record) => (int) $record->leads > 0 ? number_format((float) $record->spend / (int) $record->leads, 2).' '.$record->currency : '—'),
                TextColumn::make('reach')->label(Studio::text('meta_ads_reach'))->numeric()->sortable(),
                TextColumn::make('clicks')->label(Studio::text('meta_ads_clicks_label'))->numeric()->sortable(),
                TextColumn::make('impressions')->label(Studio::text('meta_ads_impressions'))->numeric()->sortable()->toggleable(),
            ])
            ->defaultSort('spend', 'desc')
            ->defaultKeySort(false)
            ->paginated([5, 10, 25]);
    }
}
