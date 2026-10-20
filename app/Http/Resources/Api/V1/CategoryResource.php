<?php

namespace App\Http\Resources\Api\V1;

use App\StudioRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A service, project or post category.
 *
 * @mixin StudioRecord
 */
class CategoryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->titleText(),
            'slug' => $this->text('slug'),
            'description' => $this->text('description'),
            'is_featured' => (bool) $this->is_featured,
            'share_url' => $this->publicUrl(),
        ];
    }
}
