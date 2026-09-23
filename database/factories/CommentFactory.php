<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Content;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
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
