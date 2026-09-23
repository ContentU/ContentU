<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Quarter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quarter>
 */
class QuarterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = 2026;
        $n = fake()->numberBetween(1, 4);

        return [
            'client_id' => Client::factory(),
            'year' => $year,
            'quarter_number' => $n,
            'status' => 'draft',
            ...Quarter::deriveDates($year, $n),
        ];
    }

    /** Trimestre preciso, con label e date coerenti. */
    public function period(int $year, int $quarterNumber): static
    {
        return $this->state(fn () => [
            'year' => $year,
            'quarter_number' => $quarterNumber,
            ...Quarter::deriveDates($year, $quarterNumber),
        ]);
    }

    public function inReview(): static
    {
        return $this->state(fn () => ['status' => 'in_review']);
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved']);
    }

    public function closed(): static
    {
        return $this->state(fn () => ['status' => 'closed']);
    }
}
