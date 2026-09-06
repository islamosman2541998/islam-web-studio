<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        $data = ['name' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'alt' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'caption' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'visibility' => 'public', 'kind' => 'image', 'is_active' => true, 'sort_order' => 0];

        return $data;
    }
}
