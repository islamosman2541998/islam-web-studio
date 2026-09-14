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

    /** A public, active image by id, looked up at most once per request (layout, brand, SEO and preloader share the same logos). */
    public static function publicImage(mixed $id): ?self
    {
        if (! is_numeric($id)) {
            return null;
        }

        $attributes = request()->attributes;
        $key = 'iws.public-image.'.(int) $id;
        if (! $attributes->has($key)) {
            $attributes->set($key, static::query()->where('kind', 'image')->where('visibility', 'public')->where('is_active', true)->find((int) $id));
        }

        return $attributes->get($key);
    }

    /** The stored kind (image, video, file) of an asset id, used to keep media type columns in sync. */
    public static function kindOf(mixed $id): ?string
    {
        return filled($id) ? static::query()->whereKey($id)->value('kind') : null;
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
