<?php

use App\Models\Client;
use App\Models\User;

it('updateArtifactLinks rifiuta un url non https', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $this->actingAs($admin)
        ->patch("/clients/{$client->id}/artifact-links", [
            'topics_artifact_url' => 'http://claude.ai/public/artifacts/1',
        ])
        ->assertSessionHasErrors('topics_artifact_url');
});

it('updateArtifactLinks salva i link con url validi', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $this->actingAs($admin)
        ->patch("/clients/{$client->id}/artifact-links", [
            'topics_artifact_url' => 'https://claude.ai/public/artifacts/1',
            'shooting_artifact_url' => 'https://claude.ai/public/artifacts/2',
        ])
        ->assertSessionHasNoErrors();

    expect($client->fresh()->topics_artifact_url)->toBe('https://claude.ai/public/artifacts/1');
});

it('updateArtifactLinks rifiuta i non-admin con 403', function () {
    $am = User::factory()->accountManager()->create();
    $client = Client::factory()->create();
    $am->clients()->attach($client);

    $this->actingAs($am)
        ->patch("/clients/{$client->id}/artifact-links", [
            'topics_artifact_url' => 'https://claude.ai/public/artifacts/1',
        ])
        ->assertForbidden();
});
