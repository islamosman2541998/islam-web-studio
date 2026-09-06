<?php

namespace App\Models;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceCategory extends StudioRecord
{
    protected $table = 'service_categories';

    public array $translatable = ['name', 'description', 'slug', 'meta_title', 'meta_description', 'keywords'];

    public function ogImage(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'og_image_id');
    }
}
