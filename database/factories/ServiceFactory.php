<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $data = ['name' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'short_description' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'long_description' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'slug' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'status' => 'draft', 'is_active' => true, 'is_featured' => false, 'sort_order' => 0, 'pricing_type' => 'on_request', 'meta_title' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'meta_description' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'keywords' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'seo_index' => true];
        $slug = fake()->unique()->slug(3);
        $data['slug'] = ['ar' => $slug, 'en' => $slug];

        return $data;
    }
}
