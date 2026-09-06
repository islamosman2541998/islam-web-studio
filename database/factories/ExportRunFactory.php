<?php

namespace Database\Factories;

use App\Models\ExportRun;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExportRunFactory extends Factory
{
    protected $model = ExportRun::class;

    public function definition(): array
    {
        $data = ['locale' => 'ar', 'status' => 'queued', 'row_count' => 0];

        return $data;
    }
}
