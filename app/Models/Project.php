<?php

namespace App\Models;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends StudioRecord
{
    protected $table = 'projects';

    public array $translatable = ['title', 'slug', 'duration', 'overview', 'challenge', 'solution', 'result', 'meta_title', 'meta_description', 'keywords'];

    public function gallery(): HasMany
    {
        return $this->hasMany(ProjectMedia::class)->orderBy('sort_order');
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(ProjectMetric::class)->orderBy('sort_order');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function testimonial(): BelongsTo
    {
        return $this->belongsTo(Testimonial::class);
    }

    public function projectCategory(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function mainMedia(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'main_media_id');
    }

    public function mainMediaPoster(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'main_media_poster_id');
    }

    public function mainMediaMobile(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'main_media_mobile_id');
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'og_image_id');
    }
}
