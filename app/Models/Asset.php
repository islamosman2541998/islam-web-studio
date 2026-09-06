<?php

namespace App\Models;

use App\StudioRecord;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Asset extends StudioRecord implements HasMedia
{
    protected $table = 'assets';

    public array $translatable = ['name', 'alt', 'caption'];

    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('original')->singleFile();
    }

    public function original(): ?Media
    {
        return $this->getFirstMedia('original');
    }

    public function publicUrl(string $locale = ''): string
    {
        return $this->visibility === 'public' ? ($this->original()?->getUrl() ?? '') : route('assets.private', $this);
    }

    public function imageUrl(int $width = 960): string
    {
        if ($this->visibility !== 'public') {
            return $this->publicUrl();
        } $conversions = $this->metadata['webp'] ?? [];
        if (! $conversions) {
            return $this->publicUrl();
        } $keys = array_keys($conversions);
        sort($keys);
        foreach ($keys as $key) {
            if ((int) $key >= $width) {
                return asset('storage/'.$conversions[$key]);
            }
        }

return asset('storage/'.$conversions[end($keys)]);
    }
}
