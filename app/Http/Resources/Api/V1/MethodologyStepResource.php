<?php

namespace App\Http\Resources\Api\V1;

use App\Models\MethodologyStep;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin MethodologyStep */
class MethodologyStepResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => (int) $this->number,
            'title' => $this->titleText(),
            'description' => $this->text('description'),
            'deliverable' => $this->text('deliverable'),
            'icon' => $this->icon,
        ];
    }
}
