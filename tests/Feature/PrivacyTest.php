<?php

use App\Models\Client;
use App\Models\ClientPublicLink;
use App\Models\Content;
use App\Models\Quarter;
use App\Models\User;

it('SICUREZZA — un utente cliente non accede al link di un altro cliente', function () {
    $a = Client::factory()->create(['name' => 'Cliente A']);
    $b = Client::factory()->create(['name' => 'Cliente B']);

    $linkB = ClientPublicLink::factory()->for($b)->create();

    $userA = User::factory()->client()->create();
    $userA->clients()->attach($a);

    $this->actingAs($userA)->get("/ped/{$b->slug}")->assertNotFound();
    $this->actingAs($userA)->get("/ped/{$b->slug}/feed")->assertNotFound();
    $this->actingAs($userA)->get("/ped/{$b->slug}/argomenti")->assertNotFound();
});

it('SICUREZZA — un utente cliente non raggiunge nessuna rotta interna', function () {
    $user = User::factory()->client()->create();

    foreach (['/dashboard', '/clients', '/settings/users', '/settings/content-types'] as $route) {
        $this->actingAs($user)->get($route)->assertForbidden();
    }
});

it('SICUREZZA — un cliente non può agire su un contenuto di un altro cliente', function () {
    $a = Client::factory()->create();
    $b = Client::factory()->create();

    $linkA = ClientPublicLink::factory()->for($a)->create();
    $contentB = Content::factory()->for(Quarter::factory()->for($b)->create())->approved()->create();

    $userA = User::factory()->client()->create();
    $userA->clients()->attach($a);

    $this->actingAs($userA)
        ->post("/ped/{$a->slug}/contents/{$contentB->id}/approve")
        ->assertNotFound();

    expect($contentB->fresh()->status->value)->toBe('approved');
});

it('SICUREZZA — un account manager non vede i clienti non assegnati', function () {
    $am = User::factory()->accountManager()->create();
    $mine = Client::factory()->create();
    $other = Client::factory()->create(['name' => 'Cliente Non Mio']);

    $am->clients()->attach($mine);

    $this->actingAs($am)->get('/dashboard')
        ->assertDontSee('Cliente Non Mio')
        ->assertInertia(fn ($page) => $page->has('clients', 1));

    $this->actingAs($am)->get("/clients/{$other->id}/edit")->assertForbidden();
});

it('SICUREZZA — nessuna prop Inertia pubblica contiene dati di altri clienti', function () {
    $a = Client::factory()->create();
    $b = Client::factory()->create(['name' => 'ALTRO CLIENTE', 'internal_notes' => 'NOTA RISERVATA']);

    Content::factory()->for(Quarter::factory()->for($b)->create())->approved()
        ->create(['title' => 'CONTENUTO DI B']);

    $linkA = ClientPublicLink::factory()->for($a)->create();
    Quarter::factory()->for($a)->create();

    $userA = User::factory()->client()->create();
    $userA->clients()->attach($a);

    // Ispeziona il PAYLOAD serializzato (il markup HTML con le props Inertia
    // incorporate), non solo il rendering visibile: un dato nascosto in UI
    // ma presente nel JSON è comunque una fuga. Niente header X-Inertia-Version
    // fittizio: farebbe rispondere 409 con corpo vuoto e il test passerebbe
    // sempre, a vuoto, senza controllare nulla.
    $json = $this->actingAs($userA)
        ->get("/ped/{$a->slug}/feed")
        ->getContent();

    expect($json)->not->toContain('ALTRO CLIENTE')
        ->and($json)->not->toContain('NOTA RISERVATA')
        ->and($json)->not->toContain('CONTENUTO DI B');
});
