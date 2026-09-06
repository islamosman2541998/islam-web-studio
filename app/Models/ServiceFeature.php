<?php

namespace App\Models;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceFeature extends StudioRecord
{
    protected $table = 'service_features';

    public array $translatable = ['text'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
