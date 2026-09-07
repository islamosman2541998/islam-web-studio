<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Role;
use App\Support\Studio;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function beforeSave(): void
    {
        if ($this->record->hasRole('Owner')) {
            $ownerId = (int) Role::findByName('Owner')->id;
            $selectedRoleIds = array_map('intval', $this->data['roles'] ?? []);

            if (! in_array($ownerId, $selectedRoleIds, true)) {
                throw ValidationException::withMessages(['data.roles' => Studio::text('owner_protected')]);
            }
        }
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(Studio::text('saved'))
            ->body(Studio::text('updated'));
    }
}
