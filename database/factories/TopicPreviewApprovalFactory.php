<?php

namespace Database\Factories;

use App\Models\TopicPreview;
use App\Models\TopicPreviewApproval;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TopicPreviewApproval>
 */
class TopicPreviewApprovalFactory extends Factory
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
            'status' => 'approved',
            'comment' => null,
            'responded_by' => User::factory(),
            'responded_at' => now(),
        ];
    }
}
