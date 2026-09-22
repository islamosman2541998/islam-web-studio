<?php

namespace App\Filament\Widgets\Concerns;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

trait HasMetaDateRange
{
    use InteractsWithPageFilters;

    protected function startDate(): Carbon
    {
        return Carbon::parse($this->pageFilters['startDate'] ?? today()->subDays(29))->startOfDay();
    }

    protected function endDate(): Carbon
    {
        return Carbon::parse($this->pageFilters['endDate'] ?? today())->endOfDay();
    }

    protected function inPeriod(Builder $query, string $column = 'date'): Builder
    {
        return $query
            ->whereDate($column, '>=', $this->startDate()->toDateString())
            ->whereDate($column, '<=', $this->endDate()->toDateString());
    }
}
