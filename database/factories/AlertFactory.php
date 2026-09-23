<?php

namespace Database\Factories;

use App\Enums\AlertType;
use App\Models\Alert;
use App\Models\Quarter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quarter = Quarter::factory()->create();

        return [
            'client_id' => $quarter->client_id,
            'type' => AlertType::NextQuarterMissing->value,
            'subject_type' => $quarter->getMorphClass(),
            'subject_id' => $quarter->id,
            'message' => fake()->sentence(),
        ];
    }
}
