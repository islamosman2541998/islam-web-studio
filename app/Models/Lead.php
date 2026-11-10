<?php

namespace App\Models;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends StudioRecord
{
    protected $table = 'leads';

    public array $translatable = [];

    protected function casts(): array
    {
        return [...parent::casts(), 'meta_payload' => 'array', 'meta_created_at' => 'datetime'];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(LeadMessage::class);
    }
}
