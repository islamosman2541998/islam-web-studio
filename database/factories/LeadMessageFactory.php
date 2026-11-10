<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeadMessage>
 */
class LeadMessageFactory extends Factory
{
    protected $model = LeadMessage::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory()->state(['name' => fake()->name(), 'phone' => '01012345678', 'email' => fake()->safeEmail()]),
            'channel' => 'whatsapp',
            'recipient' => '201012345678',
            'body' => fake()->sentence(),
            'status' => 'pending',
        ];
    }

    public function sent(): static
    {
        return $this->state(['status' => 'sent', 'sent_at' => now()]);
    }
}
