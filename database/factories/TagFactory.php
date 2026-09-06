<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        $data = ['name' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'slug' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'is_active' => true, 'sort_order' => 0];
        $slug = fake()->unique()->slug(3);
        $data['slug'] = ['ar' => $slug, 'en' => $slug];

        return $data;
    }
}
