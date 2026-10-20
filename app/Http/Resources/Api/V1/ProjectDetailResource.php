<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ProjectMedia;
use App\Models\ProjectMetric;
use App\Models\Service;
use App\Support\Studio;
use Illuminate\Http\Request;

class ProjectDetailResource extends ProjectResource
{
    public const WITH = [...ProjectResource::WITH, 'mainMediaMobile.media', 'gallery.media.media', 'services', 'testimonial.avatarMedia.media'];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $testimonial = $this->testimonial;
        $showTestimonial = $testimonial && $testimonial->is_approved && $testimonial->is_active && ! $testimonial->is_demo;

        return [
            ...parent::toArray($request),
            'mobile_media' => MediaResource::forAsset($this->mainMediaMobile, $this->mainMediaPoster),
            'duration' => $this->text('duration'),
            'challenge_html' => Studio::cleanHtml($this->text('challenge')),
            'solution_html' => Studio::cleanHtml($this->text('solution')),
            'result_html' => Studio::cleanHtml($this->text('result')),
            'live_url' => filled($this->live_url) && Studio::safeUrl($this->live_url) !== '#' ? Studio::safeUrl($this->live_url) : null,
            'tech_stack' => array_values($this->tech_stack ?? []),
            'gallery' => $this->gallery
                ->reject(fn (ProjectMedia $entry): bool => $entry->media_id === $this->main_media_id)
                ->map(fn (ProjectMedia $entry): ?array => ($media = MediaResource::forAsset($entry->media)) ? ['media' => $media, 'caption' => $entry->text('caption')] : null)
                ->filter()
                ->values(),
            'metrics' => $this->metrics->map(fn (ProjectMetric $metric): array => ['value' => $metric->value, 'label' => $metric->text('label')])->values(),
            'services' => $this->services
                ->filter(fn (Service $service): bool => $service->is_active && $service->status === 'published' && filled($service->titleText()))
                ->map(fn (Service $service): array => ['id' => $service->id, 'slug' => $service->text('slug'), 'name' => $service->titleText()])
                ->values(),
            'testimonial' => $showTestimonial ? TestimonialResource::make($testimonial) : null,
        ];
    }
}
