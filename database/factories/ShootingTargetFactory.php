<?php

namespace Database\Factories;

use App\Models\Quarter;
use App\Models\ShootingTarget;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShootingTarget>
 */
class ShootingTargetFactory extends Factory
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
            'quarter_id' => $quarter->id,
            'period_label' => $quarter->label,
            'ideal_sessions' => 4,
            'planned_sessions' => 3,
            'potential_sessions' => 1,
            'weight' => 'M',
            'status_note' => null,
        ];
    }
}
