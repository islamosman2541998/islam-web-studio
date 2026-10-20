<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Post */
class PostResource extends JsonResource
{
    public const WITH = ['featuredMedia.media', 'postCategory'];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $words = count(preg_split('/\s+/u', trim(strip_tags($this->text('content'))), -1, PREG_SPLIT_NO_EMPTY) ?: []);

        return [
            'id' => $this->id,
            'slug' => $this->text('slug'),
            'title' => $this->titleText(),
            'excerpt' => $this->text('excerpt'),
            'image' => MediaResource::forAsset($this->featuredMedia),
            'category' => CategoryResource::make($this->whenLoaded('postCategory', fn () => $this->postCategory)),
            'published_at' => ($this->published_at ?? $this->created_at)?->toIso8601String(),
            'reading_minutes' => max(1, (int) ceil($words / 200)),
            'is_featured' => (bool) $this->is_featured,
            'share_url' => $this->publicUrl(),
        ];
    }
}
