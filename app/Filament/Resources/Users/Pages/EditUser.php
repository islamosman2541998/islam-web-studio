<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Role;
use App\Support\Studio;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function beforeSave(): void
    {
        if ($this->record->hasRole('Owner')) {
            $ownerId = Role::findByName('Owner')->id;
            if (! in_array($ownerId, $this->data['roles'] ?? [])) {
                throw ValidationException::withMessages(['data.roles' => Studio::text('owner_protected')]);
            }
        }
    }
}
