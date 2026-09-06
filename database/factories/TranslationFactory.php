<?php

namespace Database\Factories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    public function definition(): array
    {
        $data = ['value' => ['ar' => 'محتوى تجريبي', 'en' => 'Sample content'], 'is_active' => true];
        $data['key'] = fake()->unique()->slug(3);

        return $data;
    }
}
