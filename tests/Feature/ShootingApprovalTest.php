<?php

use App\Models\Client;
use App\Models\ClientPublicLink;
use App\Models\ShootingSession;
use App\Models\User;
use App\Notifications\ClientShootingFeedback;
use Illuminate\Support\Facades\Notification;

it('il cliente approva e commenta una sessione shooting, e la notifica arriva agli admin', function () {
    Notification::fake();

    $client = Client::factory()->create();
    ClientPublicLink::factory()->for($client)->create();
    $admin = User::factory()->admin()->create();
    $session = ShootingSession::factory()->for($client)->create([
        'session_date' => today()->addWeek(),
    ]);

    $user = User::factory()->client()->create();
    $user->clients()->attach($client);

    $this->actingAs($user)
        ->post("/ped/{$client->slug}/shooting/{$session->id}/approve")
        ->assertRedirect();

    expect($session->fresh()->client_approved_at)->not->toBeNull()
        ->and($session->fresh()->client_approved_by)->toBe($user->id);

    $this->actingAs($user)
        ->post("/ped/{$client->slug}/shooting/{$session->id}/comment", ['body' => 'Possiamo spostare a mercoledì?'])
        ->assertRedirect();

    expect($session->comments()->count())->toBe(1);

    Notification::assertSentTo($admin, ClientShootingFeedback::class);
});

it('SICUREZZA — un cliente di un altro cliente riceve 404 sulla sessione shooting', function () {
    $a = Client::factory()->create();
    ClientPublicLink::factory()->for($a)->create();
    $b = Client::factory()->create();
    $session = ShootingSession::factory()->for($b)->create(['session_date' => today()->addWeek()]);

    $userA = User::factory()->client()->create();
    $userA->clients()->attach($a);

    $this->actingAs($userA)
        ->post("/ped/{$a->slug}/shooting/{$session->id}/approve")
        ->assertNotFound();
});

it('l\'admin non può approvare al posto del cliente (403)', function () {
    $client = Client::factory()->create();
    ClientPublicLink::factory()->for($client)->create();
    $admin = User::factory()->admin()->create();
    $session = ShootingSession::factory()->for($client)->create(['session_date' => today()->addWeek()]);

    $this->actingAs($admin)
        ->post("/ped/{$client->slug}/shooting/{$session->id}/approve")
        ->assertForbidden();
});

it('la modifica di data o tipo azzera l\'approvazione del cliente', function () {
    $client = Client::factory()->create();
    $admin = User::factory()->admin()->create();
    $session = ShootingSession::factory()->for($client)->create([
        'session_date' => today()->addWeek(),
        'type' => 'photo',
        'client_approved_at' => now(),
        'client_approved_by' => User::factory()->client()->create()->id,
    ]);

    $this->actingAs($admin)
        ->put("/shooting/sessions/{$session->id}", [
            'session_date' => today()->addWeeks(3)->toDateString(),
            'type' => 'video',
        ])
        ->assertRedirect();

    expect($session->fresh()->client_approved_at)->toBeNull()
        ->and($session->fresh()->client_approved_by)->toBeNull();
});
