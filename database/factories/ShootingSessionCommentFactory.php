<?php

namespace Database\Factories;

use App\Models\ShootingSession;
use App\Models\ShootingSessionComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShootingSessionComment>
 */
class ShootingSessionCommentFactory extends Factory
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
            'author_id' => User::factory(),
            'author_role' => 'team',
            'body' => fake()->sentence(),
        ];
    }

    public function fromClient(): static
    {
        return $this->state(fn () => ['author_role' => 'client']);
    }
}
