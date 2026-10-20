<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Page */
class PageResource extends JsonResource
{
    public const WITH = ['heroMedia.media', 'heroPoster.media'];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->text('slug'),
            'title' => $this->titleText(),
            'excerpt' => $this->text('excerpt'),
            'template' => $this->template,
            'hero' => MediaResource::forAsset($this->heroMedia, $this->heroPoster),
            'share_url' => $this->publicUrl(),
        ];
    }
}
