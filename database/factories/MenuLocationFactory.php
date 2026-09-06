<?php

namespace Database\Factories;

use App\Models\MenuLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuLocationFactory extends Factory
{
    protected $model = MenuLocation::class;

    public function definition(): array
    {
        $data = ['name' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'is_active' => true, 'sort_order' => 0];
        $data['key'] = fake()->unique()->slug(3);

        return $data;
    }
}
