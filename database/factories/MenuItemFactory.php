<?php

namespace Database\Factories;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        $data = ['title' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'type' => 'route', 'dynamic_mode' => 'selected', 'dynamic_featured' => false, 'dynamic_limit' => 8, 'target_blank' => false, 'visibility' => 'all', 'is_active' => true, 'sort_order' => 0];

        return $data;
    }
}
