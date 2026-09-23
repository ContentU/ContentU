<?php

use App\Models\Client;
use App\Models\Content;
use App\Models\Quarter;
use App\Models\User;

it('reindirizza un ospite alla pagina di login', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

it('fa vedere la dashboard a un utente interno autenticato', function () {
    $user = User::factory()->accountManager()->create();

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
});

it('conta i clienti attivi correttamente', function () {
    $admin = User::factory()->admin()->create();

    Client::factory()->count(3)->create(['status' => 'active']);
    Client::factory()->paused()->create();
    Client::factory()->archived()->create();

    $this->actingAs($admin)->get('/dashboard')
        ->assertInertia(fn ($page) => $page->where('stats.activeClients', 3));
});

it('calcola la salute PED del trimestre corrente', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $quarter = Quarter::factory()->for($client)->create([
        'starts_on' => today()->subMonth(), 'ends_on' => today()->addMonth(),
    ]);

    Content::factory()->for($quarter)->count(3)->scheduled()->create();
    Content::factory()->for($quarter)->create(['status' => 'draft']);

    $this->actingAs($admin)->get('/dashboard')
        ->assertInertia(fn ($page) => $page->where('clients.0.health', 75));
});

it('un admin invita un utente interno e non può crearne uno con ruolo cliente', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post('/settings/users', [
        'name' => 'Nuovo AM', 'email' => 'nuovo@contentu.local', 'role' => 'account_manager',
    ])->assertSessionHasNoErrors();

    expect(User::firstWhere('email', 'nuovo@contentu.local')->role->value)->toBe('account_manager');

    $this->actingAs($admin)->post('/settings/users', [
        'name' => 'Finto cliente', 'email' => 'x@y.it', 'role' => 'client',
    ])->assertSessionHasErrors('role');
});

it('la disattivazione blocca l accesso ma non cancella l utente', function () {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->accountManager()->create();

    $this->actingAs($admin)->patch("/settings/users/{$target->id}/toggle-active");

    expect($target->fresh()->is_active)->toBeFalse()
        ->and(User::find($target->id))->not->toBeNull();

    $this->actingAs($target->fresh())->get('/dashboard')->assertForbidden();
});

it('un admin non può disattivare se stesso', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->patch("/settings/users/{$admin->id}/toggle-active")
        ->assertStatus(422);
});
