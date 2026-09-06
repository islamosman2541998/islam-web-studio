<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->hasRole('Owner');
    }

    public function view(User $user, User $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, User $record): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, User $record): bool
    {
        return $this->viewAny($user) && $user->id !== $record->id && ! $record->hasRole('Owner');
    }

    public function deleteAny(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function restore(User $user, User $record): bool
    {
        return $this->viewAny($user);
    }

    public function restoreAny(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function forceDelete(User $user, User $record): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
