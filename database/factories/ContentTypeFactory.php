<?php

namespace Database\Factories;

use App\Models\ContentType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentType>
 */
class ContentTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(1),
            'label' => fake()->word(),
            'requires_secondary_asset' => false,
            'is_active' => true,
        ];
    }

    public function withCover(): static
    {
        return $this->state(fn () => ['requires_secondary_asset' => true]);
    }
}
