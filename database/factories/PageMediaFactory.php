<?php

namespace Database\Factories;

use App\Models\PageMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageMediaFactory extends Factory
{
    protected $model = PageMedia::class;

    public function definition(): array
    {
        $data = ['kind' => 'gallery_image', 'caption' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'sort_order' => 0];

        return $data;
    }
}
