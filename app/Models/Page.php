<?php

namespace App\Models;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends StudioRecord
{
    protected $table = 'pages';

    public array $translatable = ['title', 'slug', 'excerpt', 'content', 'meta_title', 'meta_description', 'keywords'];

    public function gallery(): HasMany
    {
        return $this->hasMany(PageMedia::class)->orderBy('sort_order');
    }

    public function heroMedia(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'hero_media_id');
    }

    public function heroPoster(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'hero_poster_id');
    }

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'og_image_id');
    }
}
