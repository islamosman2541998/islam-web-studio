<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A public image, video or file. Hidden or inactive assets resolve to null.
 *
 * @mixin Asset
 */
class MediaResource extends JsonResource
{
    public ?Asset $poster = null;

    public static function forAsset(?Asset $asset, ?Asset $poster = null): ?self
    {
        if (! self::isPublic($asset)) {
            return null;
        }

        $resource = new self($asset);
        $resource->poster = self::isPublic($poster) && $poster->kind === 'image' ? $poster : null;

        return $resource;
    }

    public static function isPublic(?Asset $asset): bool
    {
        return $asset !== null && $asset->visibility === 'public' && $asset->is_active;
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $isImage = $this->kind === 'image';
        $metadata = $this->metadata ?? [];

        return [
            'id' => $this->id,
            'kind' => $this->kind,
            'mime' => $metadata['mime'] ?? null,
            'url' => $isImage ? $this->imageUrl(1920) : $this->publicUrl(),
            'thumbnail_url' => $isImage ? $this->imageUrl(640) : $this->poster?->imageUrl(640),
            'original_url' => $this->publicUrl(),
            'sizes' => $isImage ? (object) collect($metadata['webp'] ?? [])->map(fn (string $path): string => asset('storage/'.$path))->all() : (object) [],
            'width' => $metadata['width'] ?? null,
            'height' => $metadata['height'] ?? null,
            'placeholder' => $metadata['lqip'] ?? null,
            'alt' => $this->text('alt') ?: $this->titleText(),
            'poster' => $this->poster ? self::forAsset($this->poster) : null,
        ];
    }
}
