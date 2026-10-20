<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Slide;
use App\Support\Studio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Slide */
class SlideResource extends JsonResource
{
    public const WITH = ['desktopMedia.media', 'desktopPoster.media', 'mobileMedia.media', 'mobilePoster.media'];

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $buttonText = $this->text('button_text');
        $buttonUrl = Studio::safeUrl(str_replace('{locale}', app()->getLocale(), (string) $this->button_url));

        return [
            'id' => $this->id,
            'title' => $this->text('title'),
            'description' => $this->text('description'),
            'button' => filled($buttonText) && $buttonUrl !== '#' ? ['text' => $buttonText, 'url' => $buttonUrl] : null,
            'media' => MediaResource::forAsset($this->desktopMedia, $this->desktopPoster),
            'mobile_media' => MediaResource::forAsset($this->mobileMedia, $this->mobilePoster),
            'colors' => [
                'text' => Studio::color($this->text_color, '#F7F4D5'),
                'button' => Studio::color($this->button_color, '#D3968C'),
                'button_text' => Studio::color($this->button_text_color, '#0A3323'),
                'button_hover' => Studio::color($this->button_hover_color, '#F7F4D5'),
            ],
            'overlay_opacity' => min(80, max(0, (int) $this->overlay_opacity)) / 100,
            'video_advance' => $this->video_advance,
        ];
    }
}
