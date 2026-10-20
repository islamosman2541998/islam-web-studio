<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Tag;
use App\Support\Studio;
use Illuminate\Http\Request;

class PostDetailResource extends PostResource
{
    public const WITH = [...PostResource::WITH, 'author', 'tags'];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'content_html' => Studio::cleanHtml($this->text('content')),
            'author' => $this->author ? ['name' => $this->author->name] : null,
            'tags' => $this->tags
                ->filter(fn (Tag $tag): bool => $tag->is_active && filled($tag->titleText()))
                ->map(fn (Tag $tag): array => ['id' => $tag->id, 'name' => $tag->titleText(), 'slug' => $tag->text('slug')])
                ->values(),
        ];
    }
}
