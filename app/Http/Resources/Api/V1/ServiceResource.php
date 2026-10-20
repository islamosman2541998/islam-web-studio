<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Service */
class ServiceResource extends JsonResource
{
    /** Relations every service card needs (the nested `media` avoids one query per image). */
    public const WITH = ['imageMedia.media', 'serviceCategory'];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->text('slug'),
            'name' => $this->titleText(),
            'short_description' => $this->text('short_description'),
            'image' => MediaResource::forAsset($this->imageMedia),
            'category' => CategoryResource::make($this->whenLoaded('serviceCategory', fn () => $this->serviceCategory)),
            'pricing_type' => $this->pricing_type,
            'is_featured' => (bool) $this->is_featured,
            'share_url' => $this->publicUrl(),
        ];
    }
}
