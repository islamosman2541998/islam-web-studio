<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $data = ['title' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'slug' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'status' => 'draft', 'is_active' => true, 'is_featured' => false, 'sort_order' => 0, 'duration' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'overview' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'challenge' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'solution' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'result' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'main_media_type' => 'image', 'meta_title' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'meta_description' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'keywords' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'seo_index' => true];
        $slug = fake()->unique()->slug(3);
        $data['slug'] = ['ar' => $slug, 'en' => $slug];

        return $data;
    }
}
