<?php

namespace Database\Factories;

use App\Models\ProjectMetric;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectMetricFactory extends Factory
{
    protected $model = ProjectMetric::class;

    public function definition(): array
    {
        $data = ['label' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'sort_order' => 0];

        return $data;
    }
}
