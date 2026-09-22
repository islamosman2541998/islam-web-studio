<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasMetaDateRange;
use App\Models\MetaAdInsight;
use App\Support\Studio;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class MetaCampaignPerformanceTable extends TableWidget
{
    use HasMetaDateRange;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('leads.view');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(Studio::text('meta_campaign_performance'))
            ->description(Studio::text('meta_campaign_performance_description'))
            ->query(fn (): Builder => $this->inPeriod(MetaAdInsight::query())
                ->selectRaw('MIN(id) as id, campaign_id, campaign_name, currency, SUM(spend) as spend, SUM(leads) as leads, SUM(reach) as reach, SUM(impressions) as impressions, SUM(clicks) as clicks, SUM(link_clicks) as link_clicks')
                ->groupBy('campaign_id', 'campaign_name', 'currency'))
            ->columns([
                TextColumn::make('campaign_name')->label(Studio::text('meta_campaign'))->searchable()->wrap(),
                TextColumn::make('spend')->label(Studio::text('meta_ads_spend'))->formatStateUsing(fn ($state, $record) => number_format((float) $state, 2).' '.$record->currency)->sortable(),
                TextColumn::make('leads')->label(Studio::text('meta_ads_leads'))->numeric()->sortable(),
                TextColumn::make('cost_per_lead')->label(Studio::text('meta_ads_cost_per_lead'))->state(fn ($record) => (int) $record->leads > 0 ? number_format((float) $record->spend / (int) $record->leads, 2).' '.$record->currency : '—'),
                TextColumn::make('ctr_calculated')->label(Studio::text('meta_ctr'))->state(fn ($record) => (int) $record->impressions > 0 ? number_format((int) $record->clicks / (int) $record->impressions * 100, 2).'%' : '—'),
                TextColumn::make('conversion')->label(Studio::text('meta_click_to_lead'))->state(fn ($record) => (int) $record->clicks > 0 ? number_format((int) $record->leads / (int) $record->clicks * 100, 2).'%' : '—'),
                TextColumn::make('reach')->label(Studio::text('meta_ads_reach'))->numeric()->sortable()->toggleable(),
                TextColumn::make('clicks')->label(Studio::text('meta_ads_clicks_label'))->numeric()->sortable()->toggleable(),
            ])
            ->defaultSort('spend', 'desc')->defaultKeySort(false)->paginated([5, 10, 25]);
    }
}
