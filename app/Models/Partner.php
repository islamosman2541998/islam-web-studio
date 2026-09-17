<?php

namespace App\Models;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Partner extends StudioRecord
{
    protected $table = 'partners';

    public array $translatable = ['title'];

    public function image(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'image_id');
    }
}
