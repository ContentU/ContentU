<?php

namespace Database\Factories;

use App\Models\Content;
use App\Models\ContentType;
use App\Models\Quarter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Content>
 */
class ContentFactory extends Factory
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
            'content_type_id' => ContentType::factory(),
            'title' => fake()->sentence(3),
            'caption' => fake()->paragraph(),
            'hashtags' => '#contentu #ped',
            'resource_url' => 'https://drive.example.com/'.fake()->uuid(),
            'publish_at' => fake()->dateTimeBetween('now', '+3 months'),
            'status' => 'draft',
            'channels' => ['instagram'],
        ];
    }

    public function incomplete(): static
    {
        return $this->state(fn () => ['resource_url' => null, 'caption' => null]);
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'approved']);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => ['status' => 'scheduled']);
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => 'published']);
    }
}
