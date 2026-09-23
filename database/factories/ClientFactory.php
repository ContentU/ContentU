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

    /** Anagrafica completa: brand, più referenti, tone of voice e note. Utile per i dati demo. */
    public function detailed(): static
    {
        return $this->state(fn () => [
            'brand_name' => fake()->optional(0.6)->word(),
            'contacts' => collect(range(1, fake()->numberBetween(1, 3)))->map(fn () => [
                'name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'phone' => fake()->optional()->phoneNumber(),
                'role' => fake()->randomElement(['Referente marketing', 'CEO', 'Social media manager', 'Ufficio stampa']),
            ])->all(),
            'brand_colors' => [fake()->hexColor(), fake()->hexColor()],
            'tone_of_voice' => fake()->randomElement([
                'Informale e diretto, dà del tu. Evitare tecnicismi.',
                'Istituzionale ma caldo. Niente emoji.',
                'Ironico, frasi brevi, molte domande al lettore.',
            ]),
            'internal_notes' => fake()->optional()->sentence(),
            'shooting_notes' => fake()->optional()->sentence(),
        ]);
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
