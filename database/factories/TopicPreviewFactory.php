<?php

namespace Database\Factories;

use App\Models\Quarter;
use App\Models\TopicPreview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TopicPreview>
 */
class TopicPreviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quarter_id' => Quarter::factory(),
            'month_label' => fake()->unique()->monthName(),
            'month_order' => fake()->unique()->numberBetween(1, 12),
            'status' => 'draft',
            'note' => fake()->sentence(),
        ];
    }

    public function ready(): static
    {
        return $this->state(fn () => ['status' => 'ready']);
    }
}
