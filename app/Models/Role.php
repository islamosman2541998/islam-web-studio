<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\PermissionRegistrar;

class Role extends \Spatie\Permission\Models\Role
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::restored(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());
    }
}
