<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ContentPolicy
{
    protected string $module;

    protected function allowed(User $user, string $action): bool
    {
        return $user->is_active && $user->can($this->module.'.'.$action);
    }

    public function viewAny(User $user): bool
    {
        return $this->allowed($user, 'view');
    }

    public function view(User $user, Model $record): bool
    {
        return $this->viewAny($user) && ($this->module !== 'export_runs' || $record->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $this->module !== 'export_runs' && $this->allowed($user, 'create');
    }

    public function update(User $user, Model $record): bool
    {
        return $this->module !== 'export_runs' && $this->allowed($user, 'update');
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->allowed($user, 'delete');
    }

    public function deleteAny(User $user): bool
    {
        return $this->allowed($user, 'delete');
    }

    public function restore(User $user, Model $record): bool
    {
        return $this->allowed($user, 'delete');
    }

    public function restoreAny(User $user): bool
    {
        return $this->allowed($user, 'delete');
    }

    public function forceDelete(User $user, Model $record): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function reorder(User $user): bool
    {
        return $this->allowed($user,'update');
    }
}
