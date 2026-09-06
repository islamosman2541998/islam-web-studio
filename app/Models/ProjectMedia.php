<?php

namespace App\Models;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMedia extends StudioRecord
{
    protected $table = 'project_media';

    public array $translatable = ['caption'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'media_id');
    }
}
