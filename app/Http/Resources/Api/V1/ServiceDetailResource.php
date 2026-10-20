<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ServiceFeature;
use App\Models\ServicePackage;
use App\Support\Studio;
use Illuminate\Http\Request;

class ServiceDetailResource extends ServiceResource
{
    public const WITH = [...ServiceResource::WITH, 'features', 'packages'];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'description_html' => Studio::cleanHtml($this->text('long_description')),
            'features' => $this->features->map(fn (ServiceFeature $feature): string => $feature->text('text'))->filter()->values(),
            'packages' => $this->packages->map(fn (ServicePackage $package): array => [
                'id' => $package->id,
                'name' => $package->text('name'),
                'price' => $package->price !== null ? (float) $package->price : null,
                'currency' => $package->currency,
                'features' => collect(preg_split('/\R/u', $package->text('features')))->map(fn (string $line): string => trim($line, " \t-•"))->filter()->values(),
            ])->values(),
        ];
    }
}
