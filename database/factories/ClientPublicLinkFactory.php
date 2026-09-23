<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ClientPublicLink;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ClientPublicLink>
 */
class ClientPublicLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'token' => Str::random(48),
            'visibility' => 'approved_only',
            'revoked_at' => null,
        ];
    }
}
