<?php

namespace Database\Factories;

use App\Models\MethodologyStep;
use Illuminate\Database\Eloquent\Factories\Factory;

class MethodologyStepFactory extends Factory
{
    protected $model = MethodologyStep::class;

    public function definition(): array
    {
        $data = ['title' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'description' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'deliverable' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'number' => 1, 'sort_order' => 0, 'is_active' => true];

        return $data;
    }
}
