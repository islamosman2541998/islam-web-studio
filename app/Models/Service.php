<?php

namespace App\Models;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends StudioRecord
{
    protected $table = 'services';

    public array $translatable = ['name', 'short_description', 'long_description', 'slug', 'meta_title', 'meta_description', 'keywords'];

    public function features(): HasMany
    {
        return $this->hasMany(ServiceFeature::class)->orderBy('sort_order');
    }

    public function packages(): HasMany
    {
        return $this->hasMany(ServicePackage::class)->orderBy('sort_order');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }

    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'image_media_id');
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'og_image_id');
    }
}
