<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        $data = ['source' => 'form', 'status' => 'new', 'locale' => 'ar', 'is_demo' => false];

        return $data;
    }
}
