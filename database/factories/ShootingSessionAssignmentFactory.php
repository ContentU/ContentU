<?php

namespace Database\Factories;

use App\Models\ShootingSession;
use App\Models\ShootingSessionAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShootingSessionAssignment>
 */
class ShootingSessionAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shooting_session_id' => ShootingSession::factory(),
            'user_id' => User::factory()->accountManager(),
            'role' => fake()->randomElement(['photo', 'video', 'coordination']),
            'is_alternative' => false,
        ];
    }
}
