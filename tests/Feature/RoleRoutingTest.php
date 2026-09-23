<?php

use App\Models\User;

it('manda un admin alla dashboard dopo il login', function () {
    $admin = User::factory()->admin()->create(['password' => bcrypt('secret123')]);

    $this->post('/login', ['email' => $admin->email, 'password' => 'secret123'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($admin);
});

it('manda un account manager alla dashboard dopo il login', function () {
    $am = User::factory()->accountManager()->create(['password' => bcrypt('secret123')]);

    $this->post('/login', ['email' => $am->email, 'password' => 'secret123'])
        ->assertRedirect(route('dashboard'));
});

it('non lascia entrare un cliente esterno nell area interna', function () {
    $client = User::factory()->client()->create(['password' => bcrypt('secret123')]);

    $this->post('/login', ['email' => $client->email, 'password' => 'secret123'])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('blocca un utente disattivato sulle rotte protette da ruolo', function () {
    $user = User::factory()->admin()->inactive()->create();

    $this->actingAs($user)->get('/dashboard')->assertForbidden();
});

it('nega la registrazione senza un link PED valido in sessione', function () {
    $this->post('/register', [
        'name' => 'Tizio',
        'email' => 'tizio@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertForbidden();

    expect(User::where('email', 'tizio@example.com')->exists())->toBeFalse();
});
