<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'password', 'is_active', 'locale', 'theme', 'avatar_media_id'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed', 'is_active' => 'boolean'];
    }

    public function getFilamentAvatarUrl(): ?string
    {
        $avatar = Asset::where('visibility', 'public')->where('is_active', true)->where('kind', 'image')->find($this->avatar_media_id);

        return $avatar?->imageUrl(320) ?: asset('brand/mark.svg');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->hasPermissionTo('dashboard.view');
    }
}
