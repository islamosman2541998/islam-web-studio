<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->hasRole('Owner');
    }

    public function view(User $user, Role $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Role $record): bool
    {
        return $this->viewAny($user) && $record->name !== 'Owner';
    }

    public function delete(User $user, Role $record): bool
    {
        return $this->update($user, $record) && $record->users()->count() === 0;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, Role $record): bool
    {
        return $this->update($user, $record);
    }

    public function restoreAny(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function forceDelete(User $user, Role $record): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
