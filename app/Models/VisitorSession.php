<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class VisitorSession extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'consented_at' => 'datetime',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function pageViews(): HasMany
    {
        return $this->hasMany(VisitorPageView::class)->orderBy('viewed_at');
    }

    public function events(): HasMany
    {
        return $this->hasMany(VisitorEvent::class)->orderBy('occurred_at');
    }

    public function deleteWithAnalyticsData(): void
    {
        DB::transaction(function (): void {
            $this->events()->delete();
            $this->pageViews()->delete();
            $this->delete();
        });
    }
}
