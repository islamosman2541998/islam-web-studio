<?php

namespace App\Filament\Pages;

use App\Support\ContentValidation;
use App\Support\Studio;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

abstract class ContentEdit extends EditRecord
{
    protected function getHeaderActions(): array
    {
        return [Action::make('preview')->label(Studio::text('preview'))->icon('heroicon-o-arrow-top-right-on-square')->url(fn () => $this->record->publicUrl())->openUrlInNewTab()->visible(fn () => isset($this->record->definition()['public'])), DeleteAction::make(), RestoreAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($this->record->translatable as $field) {
            $data[$field] = $this->record->getTranslations($field);
        }

return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ContentValidation::prepare(static::getResource()::module(), $data, $this->record);
    }
}
