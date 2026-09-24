<?php

use App\Models\Client;
use App\Models\ClientPublicLink;
use App\Models\Content;
use App\Models\Quarter;
use App\Models\ShootingSession;
use App\Models\User;

it('l\'admin nel PED riceve canEdit = true su argomenti, feed e shooting', function () {
    $client = Client::factory()->create();
    ClientPublicLink::factory()->for($client)->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get("/ped/{$client->slug}/argomenti")
        ->assertInertia(fn ($page) => $page->where('actions.canEdit', true));

    $this->actingAs($admin)->get("/ped/{$client->slug}/feed")
        ->assertInertia(fn ($page) => $page->where('actions.canEdit', true));

    $this->actingAs($admin)->get("/ped/{$client->slug}/shooting")
        ->assertInertia(fn ($page) => $page->where('actions.canEdit', true));
});

it('il cliente riceve canEdit = false e un suo PUT su un contenuto risponde 403', function () {
    $client = Client::factory()->create();
    ClientPublicLink::factory()->for($client)->create();
    $quarter = Quarter::factory()->for($client)->create();
    $content = Content::factory()->for($quarter)->create();

    $user = User::factory()->client()->create();
    $user->clients()->attach($client);

    $this->actingAs($user)->get("/ped/{$client->slug}/feed")
        ->assertInertia(fn ($page) => $page->where('actions.canEdit', false));

    $this->actingAs($user)
        ->put("/contents/{$content->id}", ['title' => 'Nuovo titolo'])
        ->assertForbidden();
});

it('l\'admin aggiorna una sessione shooting dal PED', function () {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $session = ShootingSession::factory()->for($client)->create([
        'session_date' => today()->addWeek(),
        'type' => 'photo',
    ]);

    $this->actingAs($admin)
        ->put("/shooting/sessions/{$session->id}", [
            'session_date' => today()->addWeeks(2)->toDateString(),
            'type' => 'video',
        ])
        ->assertRedirect();

    expect($session->fresh()->type->value)->toBe('video');
});

it('l\'account_manager non può modificare nel PED: canEdit resta false', function () {
    $client = Client::factory()->create();
    ClientPublicLink::factory()->for($client)->create();
    $am = User::factory()->accountManager()->create();
    $am->clients()->attach($client);

    $this->actingAs($am)->get("/ped/{$client->slug}/feed")
        ->assertInertia(fn ($page) => $page->where('actions.canEdit', false));
});
