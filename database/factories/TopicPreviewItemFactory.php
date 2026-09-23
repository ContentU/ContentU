<?php

namespace Database\Factories;

use App\Models\ContentType;
use App\Models\TopicPreview;
use App\Models\TopicPreviewItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TopicPreviewItem>
 */
class TopicPreviewItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'topic_preview_id' => TopicPreview::factory(),
            'content_type_id' => ContentType::factory(),
            'format_label' => 'Carosello fotografico',
            'period_label' => 'Prima metà',
            'title' => fake()->sentence(4),
            'theme' => fake()->words(2, true),
            'objective' => fake()->sentence(),
            'sort_order' => 0,
        ];
    }
}
