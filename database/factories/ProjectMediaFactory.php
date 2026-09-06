<?php

namespace Database\Factories;

use App\Models\ProjectMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectMediaFactory extends Factory
{
    protected $model = ProjectMedia::class;

    public function definition(): array
    {
        $data = ['type' => 'image', 'caption' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'sort_order' => 0];

        return $data;
    }
}
