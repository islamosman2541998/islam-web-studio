<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Testimonial */
class TestimonialResource extends JsonResource
{
    public const WITH = ['avatarMedia.media'];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_name' => $this->client_name,
            'client_company' => $this->text('client_company'),
            'quote' => $this->text('quote'),
            'rating' => max(1, min(5, (int) ($this->rating ?: 5))),
            'avatar' => MediaResource::forAsset($this->avatarMedia),
            'project_id' => $this->project_id,
            'is_featured' => (bool) $this->is_featured,
        ];
    }
}
