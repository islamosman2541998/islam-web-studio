<?php

namespace App\Models;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePackage extends StudioRecord
{
    protected $table = 'service_packages';

    public array $translatable = ['name', 'features'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
}
