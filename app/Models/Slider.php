<?php

namespace App\Models;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slider extends StudioRecord
{
    protected $table = 'sliders';

    public array $translatable = [];

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $slider): void {
            $slider->location = 'home_hero';

            if ($slider->is_active) {
                static::query()
                    ->when($slider->exists, fn (Builder $query) => $query->whereKeyNot($slider->getKey()))
                    ->update(['is_active' => false]);
            }
        });
    }

    public function scopeActiveHomeHero(Builder $query): Builder
    {
        return $query
            ->where('location', 'home_hero')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function slides(): HasMany
    {
        return $this->hasMany(Slide::class)->orderBy('sort_order');
    }
}
