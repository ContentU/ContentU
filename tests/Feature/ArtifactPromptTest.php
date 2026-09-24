<?php

use App\Models\Client;
use App\Models\Quarter;
use App\Models\TopicPreview;
use App\Models\TopicPreviewItem;
use App\Models\User;
use App\Support\ArtifactPromptBuilder;

it('il prompt argomenti contiene i titoli e mai le note interne', function () {
    $client = Client::factory()->create([
        'internal_notes' => 'Cliente lento a pagare, occhio alle fatture.',
    ]);
    $quarter = Quarter::factory()->for($client)->create();
    $ottobre = TopicPreview::factory()->for($quarter)->create(['month_label' => 'Ottobre', 'month_order' => 1]);

    TopicPreviewItem::factory()->for($ottobre, 'preview')->create([
        'title' => 'È arrivata la guava biologica siciliana',
    ]);

    $prompt = ArtifactPromptBuilder::forTopics($client, $quarter);

    expect($prompt)
        ->toContain('È arrivata la guava biologica siciliana')
        ->not->toContain('Cliente lento a pagare');
});

it('il prompt shooting non contiene mai internal_note delle sessioni', function () {
    $client = Client::factory()->create();

    $client->shootingSessions()->create([
        'session_date' => now()->addWeek(),
        'type' => 'photo',
        'internal_note' => 'Il cliente è sempre in ritardo, arrivare prima.',
    ]);

    $prompt = ArtifactPromptBuilder::forShooting($client);

    expect($prompt)->not->toContain('Il cliente è sempre in ritardo');
});

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
