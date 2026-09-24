<?php

use App\Models\Client;
use App\Models\ClientPublicLink;
use App\Models\Content;
use App\Models\Quarter;
use App\Models\User;
use App\Notifications\ClientActionTaken;
use App\Notifications\PedReadyForReview;
use Illuminate\Support\Facades\Notification;

it('restituisce 404 su uno slug inesistente senza rivelare nulla', function () {
    $this->get('/ped/cliente-inventato')->assertNotFound();
});

it('restituisce 404 se il cliente non ha nessun link attivo', function () {
    $client = Client::factory()->create();
    ClientPublicLink::factory()->for($client)->create(['revoked_at' => now()]);

    $this->get("/ped/{$client->slug}")->assertNotFound();
});

it('IMPEDISCE al cliente A di aprire il link del cliente B', function () {
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();

    ClientPublicLink::factory()->for($clientB)->create();

    $userA = User::factory()->client()->create();
    $userA->clients()->attach($clientA);

    $this->actingAs($userA)->get("/ped/{$clientB->slug}")->assertNotFound();
});

it('non espone mai le note interne del cliente nelle props', function () {
    $client = Client::factory()->create([
        'internal_notes' => 'SEGRETO: il cliente paga in ritardo',
        'shooting_notes' => 'SEGRETO: preferisce il martedì',
    ]);
    $link = ClientPublicLink::factory()->for($client)->create();
    Quarter::factory()->for($client)->create();

    $user = User::factory()->client()->create();
    $user->clients()->attach($client);

    $response = $this->actingAs($user)->get("/ped/{$client->slug}/feed");

    $response->assertDontSee('SEGRETO', escape: false);
    $response->assertInertia(fn ($page) => $page
        ->missing('client.internalNotes')
        ->missing('client.shootingNotes')
    );
});

it('con visibility approved_only non espone le bozze', function () {
    $client = Client::factory()->create();
    $link = ClientPublicLink::factory()->for($client)->create(['visibility' => 'approved_only']);
    $quarter = Quarter::factory()->for($client)->create();

    Content::factory()->for($quarter)->create(['status' => 'draft', 'title' => 'BOZZA RISERVATA']);
    Content::factory()->for($quarter)->approved()->create();

    $user = User::factory()->client()->create();
    $user->clients()->attach($client);

    $this->actingAs($user)->get("/ped/{$client->slug}/feed")
        ->assertInertia(fn ($page) => $page->has('contents', 1))
        ->assertDontSee('BOZZA RISERVATA');
});

it('non offre mai al cliente la modifica del testo', function () {
    $client = Client::factory()->create();
    $link = ClientPublicLink::factory()->for($client)->create();
    $quarter = Quarter::factory()->for($client)->create();
    Content::factory()->for($quarter)->approved()->create();

    $user = User::factory()->client()->create();
    $user->clients()->attach($client);

    $this->actingAs($user)->get("/ped/{$client->slug}/feed")
        ->assertInertia(fn ($page) => $page->where('actions.canEdit', false));
});

it('un rifiuto porta il contenuto in richiesta modifica e avvisa il team', function () {
    Notification::fake();

    $client = Client::factory()->create();
    $am = User::factory()->accountManager()->create();
    $client->users()->attach($am);

    $link = ClientPublicLink::factory()->for($client)->create();
    $quarter = Quarter::factory()->for($client)->create();
    $content = Content::factory()->for($quarter)->approved()->create();

    $user = User::factory()->client()->create();
    $user->clients()->attach($client);

    $this->actingAs($user)
        ->post("/ped/{$client->slug}/contents/{$content->id}/reject", [
            'comment' => 'La caption non rispecchia il tono del brand.',
        ]);

    expect($content->fresh()->status->value)->toBe('needs_changes');
    Notification::assertSentTo($am, ClientActionTaken::class);
});

it('rifiuta un rifiuto senza commento', function () {
    $client = Client::factory()->create();
    $link = ClientPublicLink::factory()->for($client)->create();
    $quarter = Quarter::factory()->for($client)->create();
    $content = Content::factory()->for($quarter)->approved()->create();

    $user = User::factory()->client()->create();
    $user->clients()->attach($client);

    $this->actingAs($user)
        ->post("/ped/{$client->slug}/contents/{$content->id}/reject", [])
        ->assertSessionHasErrors('comment');
});

it('avvisa il cliente quando il trimestre entra in revisione', function () {
    Notification::fake();

    $client = Client::factory()->create();
    $link = ClientPublicLink::factory()->for($client)->create();
    $quarter = Quarter::factory()->for($client)->create(['status' => 'draft']);
    $clientUser = User::factory()->client()->create();
    $clientUser->clients()->attach($client);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->patch("/quarters/{$quarter->id}/status", ['status' => 'in_review']);

    Notification::assertSentTo($clientUser, PedReadyForReview::class);
});

it('reindirizza alla pagina di accesso del cliente se non autenticato, invece di mostrare il feed', function () {
    $client = Client::factory()->create();
    ClientPublicLink::factory()->for($client)->create();
    Quarter::factory()->for($client)->create();

    $this->get("/ped/{$client->slug}/feed")
        ->assertRedirect(route('ped.entry', $client->slug));
});

it('reindirizza alla pagina di accesso anche su argomenti e shooting', function () {
    $client = Client::factory()->create();
    ClientPublicLink::factory()->for($client)->create();

    $this->get("/ped/{$client->slug}/argomenti")->assertRedirect(route('ped.entry', $client->slug));
    $this->get("/ped/{$client->slug}/shooting")->assertRedirect(route('ped.entry', $client->slug));
});
