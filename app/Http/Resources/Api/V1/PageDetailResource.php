<?php

namespace App\Http\Resources\Api\V1;

use App\Models\PageMedia;
use App\Support\Studio;
use Illuminate\Http\Request;

class PageDetailResource extends PageResource
{
    public const WITH = [...PageResource::WITH, 'gallery.media.media'];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $items = $this->gallery
            ->map(fn (PageMedia $entry): ?array => ($media = MediaResource::forAsset($entry->media)) ? ['media' => $media, 'caption' => $entry->text('caption'), 'kind' => $entry->kind] : null)
            ->filter();

        return [
            ...parent::toArray($request),
            'content_html' => Studio::cleanHtml($this->text('content')),
            'gallery' => $items->where('kind', '!=', 'file')->map(fn (array $item): array => ['media' => $item['media'], 'caption' => $item['caption']])->values(),
            'downloads' => $items->where('kind', 'file')->map(fn (array $item): array => ['media' => $item['media'], 'caption' => $item['caption']])->values(),
        ];
    }
}
