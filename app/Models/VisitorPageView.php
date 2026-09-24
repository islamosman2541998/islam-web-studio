<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitorPageView extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['viewed_at' => 'datetime', 'last_activity_at' => 'datetime'];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(VisitorSession::class, 'visitor_session_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(VisitorEvent::class);
    }
}
