<?php

namespace App\Filament\Forms\Components;

use App\Models\Asset;
use Filament\Forms\Components\Repeater;
use Filament\Support\Components\Attributes\ExposedLivewireMethod;

class ProjectGalleryRepeater extends Repeater
{
    /** @param array<int, int|string> $ids */
    #[ExposedLivewireMethod]
    public function appendMedia(array $ids): int
    {
        $ids = collect($ids)
            ->filter(fn ($id) => filter_var($id, FILTER_VALIDATE_INT) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->take(40)
            ->values();

        if ($ids->isEmpty()) {
            return 0;
        }

        $assets = Asset::query()
            ->whereKey($ids)
            ->where('visibility', 'public')
            ->where('is_active', true)
            ->whereIn('kind', ['image', 'video'])
            ->get()
            ->keyBy('id');

        $items = $this->getRawState() ?? [];
        $existing = collect($items)->pluck('media_id')->map(fn ($id) => (int) $id)->all();
        $addedKeys = [];

        foreach ($ids as $id) {
            if (! isset($assets[$id]) || in_array($id, $existing, true)) {
                continue;
            }

            $key = $this->generateUuid() ?? count($items);
            $items[$key] = [
                'media_id' => $id,
                'type' => $assets[$id]->kind,
                'caption' => ['ar' => '', 'en' => ''],
            ];
            $addedKeys[] = $key;
            $existing[] = $id;
        }

        if (! $addedKeys) {
            return 0;
        }

        $this->rawState($items);
        foreach ($addedKeys as $key) {
            $this->getChildSchema($key)->fill($items[$key]);
        }
        $this->collapsed(false, shouldMakeComponentCollapsible: false);
        $this->callAfterStateUpdated();
        $this->partiallyRender();

        return count($addedKeys);
    }
}
