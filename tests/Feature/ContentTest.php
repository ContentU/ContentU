<?php

use App\Models\Client;
use App\Models\Content;
use App\Models\ContentType;
use App\Models\Quarter;
use App\Models\User;

it('una tipologia creata dall admin compare subito nel form contenuti', function () {
    // Questa è la prova concreta del requisito "tutte le tipologie".
    $admin = User::factory()->admin()->create();
    $quarter = Quarter::factory()->create();

    $this->actingAs($admin)->post('/settings/content-types', [
        'key' => 'sondaggio',
        'label' => 'Sondaggio',
        'requires_secondary_asset' => false,
        'is_active' => true,
    ]);

    $this->actingAs($admin)
        ->get("/quarters/{$quarter->id}/contents/create")
        ->assertInertia(fn ($page) => $page
            ->where('contentTypes', fn ($types) => collect($types)->contains('label', 'Sondaggio')
            )
        );
});

it('blocca la programmazione di un Reel senza cover, nominando la cover', function () {
    $admin = User::factory()->admin()->create();
    $reel = ContentType::factory()->withCover()->create(['label' => 'Reel']);

    $content = Content::factory()->approved()->create([
        'content_type_id' => $reel->id,
        'cover_resource_url' => null,
    ]);

    $response = $this->actingAs($admin)
        ->patch("/contents/{$content->id}/status", ['status' => 'scheduled']);

    $response->assertSessionHasErrors('status');
    expect(session('errors')->first('status'))->toContain('cover');
    expect($content->fresh()->status->value)->toBe('approved');
});

it('consente la programmazione dopo aver aggiunto la cover', function () {
    $admin = User::factory()->admin()->create();
    $reel = ContentType::factory()->withCover()->create();

    $content = Content::factory()->approved()->create([
        'content_type_id' => $reel->id,
        'cover_resource_url' => 'https://drive.example.com/cover.jpg',
    ]);

    $this->actingAs($admin)
        ->patch("/contents/{$content->id}/status", ['status' => 'scheduled'])
        ->assertSessionHasNoErrors();

    expect($content->fresh()->status->value)->toBe('scheduled');
});

it('non blocca la programmazione per hashtag o tag mancanti', function () {
    $admin = User::factory()->admin()->create();

    $content = Content::factory()->approved()->create([
        'hashtags' => null,
        'content_type_id' => ContentType::factory()->create()->id,
    ]);

    $this->actingAs($admin)
        ->patch("/contents/{$content->id}/status", ['status' => 'scheduled'])
        ->assertSessionHasNoErrors();
});

it('salva un contenuto multicanale', function () {
    $admin = User::factory()->admin()->create();
    $quarter = Quarter::factory()->create();
    $type = ContentType::factory()->create();

    $this->actingAs($admin)->post("/quarters/{$quarter->id}/contents", [
        'content_type_id' => $type->id,
        'title' => 'Listino autunnale',
        'publish_at' => now()->addWeek()->toDateTimeString(),
        'channels' => ['instagram', 'facebook'],
    ]);

    expect(Content::first()->channels)->toBe(['instagram', 'facebook']);
});

it('impedisce a un copywriter di approvare un contenuto', function () {
    $copy = User::factory()->copywriter()->create();
    $client = Client::factory()->create();
    $quarter = Quarter::factory()->for($client)->create();
    $copy->clients()->attach($client);

    $content = Content::factory()->for($quarter)->create(['status' => 'in_review']);

    $this->actingAs($copy)
        ->patch("/contents/{$content->id}/status", ['status' => 'approved'])
        ->assertForbidden();
});

it('non permette di cancellare una tipologia usata da contenuti', function () {
    $admin = User::factory()->admin()->create();
    $type = ContentType::factory()->create();
    Content::factory()->create(['content_type_id' => $type->id]);

    $this->actingAs($admin)
        ->delete("/settings/content-types/{$type->id}")
        ->assertSessionHasErrors();

    expect(ContentType::find($type->id))->not->toBeNull();
});
