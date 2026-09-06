<?php

namespace Database\Factories;

use App\Models\ServicePackage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServicePackageFactory extends Factory
{
    protected $model = ServicePackage::class;

    public function definition(): array
    {
        $data = ['name' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'price' => 0, 'currency' => 'EGP', 'features' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'sort_order' => 0];

        return $data;
    }
}
