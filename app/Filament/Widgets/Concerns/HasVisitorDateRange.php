<?php

namespace App\Filament\Widgets\Concerns;

use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

trait HasVisitorDateRange
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

    protected function visitorsInPeriod(Builder $query, string $column = 'first_seen_at'): Builder
    {
        return $query->whereBetween($column, [$this->startDate(), $this->endDate()]);
    }
}
