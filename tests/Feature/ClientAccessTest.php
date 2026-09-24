<?php

use App\Models\Client;
use App\Models\ClientPublicLink;
use App\Models\User;
use App\Notifications\ClientPedInvite;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

it('nega l\'accesso a un\'email non autorizzata per quel cliente', function () {
    $client = Client::factory()->create();
    ClientPublicLink::factory()->for($client)->create();

    $this->post("/ped/{$client->slug}/check-email", ['email' => 'sconosciuto@example.com'])
        ->assertInertia(fn ($page) => $page->where('mode', 'not_allowed'));
});

it('mostra pending_invite per un\'email autorizzata che non ha ancora impostato la password', function () {
    $client = Client::factory()->create();
    ClientPublicLink::factory()->for($client)->create();
    $user = User::factory()->client()->create(['password_set_at' => null]);
    $client->users()->attach($user->id);

    $this->post("/ped/{$client->slug}/check-email", ['email' => $user->email])
        ->assertInertia(fn ($page) => $page->where('mode', 'pending_invite'));
});

it('mostra login per un\'email autorizzata che ha già impostato la password', function () {
    $client = Client::factory()->create();
    ClientPublicLink::factory()->for($client)->create();
    $user = User::factory()->client()->create(['password_set_at' => now()]);
    $client->users()->attach($user->id);

    $this->post("/ped/{$client->slug}/check-email", ['email' => $user->email])
        ->assertInertia(fn ($page) => $page->where('mode', 'login'));
});

it('l\'admin che aggiunge un\'email crea l\'utente e invia l\'invito', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $this->actingAs($admin)
        ->post("/clients/{$client->id}/access", ['email' => 'nuovo.cliente@example.com'])
        ->assertRedirect();

    $user = User::where('email', 'nuovo.cliente@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->role->value)->toBe('client');
    expect($client->pedAccessUsers()->whereKey($user->id)->exists())->toBeTrue();

    Notification::assertSentTo($user, ClientPedInvite::class);
});

it('non permette di aggiungere due volte la stessa email per lo stesso cliente', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $user = User::factory()->client()->create();
    $client->users()->attach($user->id);

    $this->actingAs($admin)
        ->post("/clients/{$client->id}/access", ['email' => $user->email])
        ->assertSessionHasErrors('email');
});

it('rimuove l\'accesso di un cliente senza cancellare l\'utente', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    ClientPublicLink::factory()->for($client)->create();
    $user = User::factory()->client()->create();
    $client->users()->attach($user->id);

    $this->actingAs($admin)
        ->delete("/clients/{$client->id}/access/{$user->id}")
        ->assertRedirect();

    expect($client->pedAccessUsers()->whereKey($user->id)->exists())->toBeFalse();
    expect(User::whereKey($user->id)->exists())->toBeTrue();

    $this->post("/ped/{$client->slug}/check-email", ['email' => $user->email])
        ->assertInertia(fn ($page) => $page->where('mode', 'not_allowed'));
});

it('dopo il reset password l\'utente risulta con password impostata', function () {
    $client = Client::factory()->create();
    $user = User::factory()->client()->create(['password_set_at' => null]);
    $client->users()->attach($user->id);

    $token = Password::broker()->createToken($user);

    $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'Password!234',
        'password_confirmation' => 'Password!234',
    ])->assertSessionHasNoErrors();

    expect($user->fresh()->password_set_at)->not->toBeNull();
});

it('salvare il form del cliente non scollega gli utenti con accesso al PED', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $pedUser = User::factory()->client()->create();
    $client->users()->attach($pedUser->id);

    $this->actingAs($admin)->put("/clients/{$client->id}", [
        'name' => $client->name,
        'status' => $client->status->value,
    ]);

    expect($client->pedAccessUsers()->whereKey($pedUser->id)->exists())->toBeTrue();
});
