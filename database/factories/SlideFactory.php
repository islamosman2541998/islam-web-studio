<?php

namespace Database\Factories;

use App\Models\Slide;
use Illuminate\Database\Eloquent\Factories\Factory;

class SlideFactory extends Factory
{
    protected $model = Slide::class;

    public function definition(): array
    {
        $data = ['title' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'description' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'desktop_media_type' => 'image', 'mobile_media_type' => 'image', 'button_text' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'button_color' => '#D3968C', 'button_text_color' => '#0A3323', 'button_hover_color' => '#F7F4D5', 'text_color' => '#F7F4D5', 'overlay_opacity' => 35, 'video_advance' => 'ended', 'sort_order' => 0, 'is_active' => true];

        return $data;
    }
}
