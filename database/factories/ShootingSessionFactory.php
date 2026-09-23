<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ShootingSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShootingSession>
 */
class ShootingSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'session_date' => fake()->dateTimeBetween('now', '+2 months'),
            'type' => fake()->randomElement(['photo', 'video', 'photo_video']),
            'is_tentative' => false,
            'checkpoint_required' => false,
            'checkpoint_note' => null,
            'internal_note' => null,
        ];
    }
}
