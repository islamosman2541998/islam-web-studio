<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $data = ['title' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'slug' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'status' => 'draft', 'is_active' => true, 'is_featured' => false, 'sort_order' => 0, 'excerpt' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'hero_type' => 'image', 'content' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'template' => 'standard', 'meta_title' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'meta_description' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'keywords' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'seo_index' => true];
        $slug = fake()->unique()->slug(3);
        $data['slug'] = ['ar' => $slug, 'en' => $slug];

        return $data;
    }
}
