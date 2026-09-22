<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'brand_name' => null,
            'status' => 'active',
            'contacts' => [[
                'name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'phone' => null,
                'role' => 'Referente marketing',
            ]],
            'internal_notes' => null,
        ];
    }

    public function paused(): static
    {
        return $this->state(fn () => ['status' => 'paused', 'paused_at' => now()]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => 'archived']);
    }
}
