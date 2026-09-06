<?php

namespace App\Models;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMetric extends StudioRecord
{
    protected $table = 'project_metrics';

    public array $translatable = ['label'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
