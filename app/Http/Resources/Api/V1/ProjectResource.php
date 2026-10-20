<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class ProjectResource extends JsonResource
{
    public const WITH = ['mainMedia.media', 'mainMediaPoster.media', 'projectCategory', 'metrics'];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $metric = $this->metrics->first();
        $media = MediaResource::forAsset($this->mainMedia, $this->mainMediaPoster);
        $isVideo = $this->mainMedia?->kind === 'video';

        return [
            'id' => $this->id,
            'slug' => $this->text('slug'),
            'title' => $this->titleText(),
            'client' => $this->client,
            'overview' => $this->text('overview'),
            'category' => CategoryResource::make($this->whenLoaded('projectCategory', fn () => $this->projectCategory)),
            'media' => $media,
            'cover' => $isVideo ? MediaResource::forAsset($this->mainMediaPoster) : $media,
            'highlight_metric' => $metric ? ['value' => $metric->value, 'label' => $metric->text('label')] : null,
            'is_featured' => (bool) $this->is_featured,
            'share_url' => $this->publicUrl(),
        ];
    }
}
