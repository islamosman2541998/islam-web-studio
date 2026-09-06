<?php

namespace Database\Factories;

use App\Models\ServiceFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFeatureFactory extends Factory
{
    protected $model = ServiceFeature::class;

    public function definition(): array
    {
        $data = ['text' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'sort_order' => 0];

        return $data;
    }
}
