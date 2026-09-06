<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        $data = ['client_company' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'quote' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'rating' => 5, 'is_approved' => false, 'is_featured' => false, 'sort_order' => 0, 'is_active' => true, 'is_demo' => false];

        return $data;
    }
}
