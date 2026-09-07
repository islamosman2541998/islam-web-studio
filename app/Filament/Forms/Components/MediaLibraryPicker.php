<?php

namespace App\Filament\Forms\Components;

use App\Models\Asset;
use App\Support\MediaPicker;
use App\Support\Studio;
use Filament\Actions\Action;
use Filament\Forms\Components\ViewField;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;

class MediaLibraryPicker extends ViewField
{
    protected string $view = 'filament.forms.components.media-library-picker';

    /** @var array<int, string> */
    protected array $mediaTypes = ['image', 'video', 'file'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerActions([
            fn (MediaLibraryPicker $component): Action => $component->getUploadMediaAction(),
        ]);
    }

    /** @param array<int, string> $types */
    public function mediaTypes(array $types): static
    {
        $this->mediaTypes = $types;

        return $this;
    }

    /** @return array<int, string> */
    public function getMediaTypes(): array
    {
        return $this->mediaTypes;
    }

    /** @return Collection<int, Asset> */
    public function getMediaAssets(): Collection
    {
        return Asset::query()
            ->with('media')
            ->where('visibility', 'public')
            ->where('is_active', true)
            ->whereIn('kind', $this->mediaTypes)
            ->latest('id')
            ->get();
    }

    public function getUploadMediaAction(): Action
    {
        return Action::make('uploadMedia')
            ->label(Studio::text('upload_new_media'))
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->outlined()
            ->schema(MediaPicker::uploadForm($this->mediaTypes))
            ->action(function (array $data): void {
                $asset = MediaPicker::createAsset($data, $this->mediaTypes);

                $this->state($asset->getKey());
            })
            ->modalHeading(Studio::text('upload_new_media'))
            ->modalDescription(Studio::text('media_upload_modal_help'))
            ->modalWidth(Width::FourExtraLarge)
            ->successNotificationTitle(Studio::text('media_upload_ready'));
    }

    public function getModalId(): string
    {
        return 'media-library-'.trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', $this->getId()), '-');
    }
}
