<?php

namespace App\Filament\Pages;

use App\Support\ContentValidation;
use Filament\Resources\Pages\CreateRecord;

abstract class ContentCreate extends CreateRecord
{
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ContentValidation::prepare(static::getResource()::module(), $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl('edit', ['record' => $this->record]);
    }
}
