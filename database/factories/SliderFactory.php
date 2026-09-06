<?php

namespace Database\Factories;

use App\Models\Slider;
use Illuminate\Database\Eloquent\Factories\Factory;

class SliderFactory extends Factory
{
    protected $model = Slider::class;

    public function definition(): array
    {
        $data = ['location' => 'home_hero', 'autoplay' => true, 'autoplay_speed' => 6500, 'draggable' => true, 'arrows' => true, 'dots' => true, 'loop' => true, 'is_active' => true, 'sort_order' => 0];

        return $data;
    }
}
