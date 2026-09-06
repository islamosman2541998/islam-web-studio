<?php

namespace App\Models;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends StudioRecord
{
    protected $table = 'testimonials';

    public array $translatable = ['client_company', 'quote'];

    public function avatarMedia(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'avatar_media_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
