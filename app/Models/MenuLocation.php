<?php

namespace App\Models;

use App\StudioRecord;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuLocation extends StudioRecord
{
    protected $table = 'menu_locations';

    public array $translatable = ['name'];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }
}
