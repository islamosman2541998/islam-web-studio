<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Partner;
use App\Support\Studio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Partner */
class PartnerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $url = Studio::safeUrl($this->url);

        return [
            'id' => $this->id,
            'title' => $this->titleText(),
            'image' => $this->image?->kind === 'image' ? MediaResource::forAsset($this->image) : null,
            'url' => $url !== '#' ? $url : null,
        ];
    }
}
