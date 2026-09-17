<?php

namespace App\Filament\Resources\Assets\Pages;

use App\Filament\Pages\ContentList;
use App\Filament\Resources\Assets\AssetResource;
use App\Support\MediaPicker;
use App\Support\Studio;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Illuminate\Validation\ValidationException;

class ListRecords extends ContentList
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        if (! AssetResource::canCreate()) {
            return parent::getHeaderActions();
        }

        return [
            Action::make('uploadBatch')
                ->label(Studio::text('media_upload_batch'))
                ->icon('heroicon-o-arrow-up-tray')
                ->schema(MediaPicker::uploadForm(['image', 'video', 'file'], true))
                ->action(function (array $data): void {
                    $result = MediaPicker::createAssets($data, ['image', 'video', 'file']);

                    if ($result['errors'] && $result['assets']) {
                        Notification::make()
                            ->title(Studio::text('media_upload_failed_title'))
                            ->body(implode("\n", $result['errors']))
                            ->danger()
                            ->persistent()
                            ->send();
                    }

                    if (! $result['assets']) {
                        throw ValidationException::withMessages(['upload_path' => $result['errors'][0] ?? Studio::text('media_processing_failed')]);
                    }
                })
                ->modalWidth(Width::FourExtraLarge)
                ->successNotificationTitle(Studio::text('media_upload_ready')),
            ...parent::getHeaderActions(),
        ];
    }
}
