<?php

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('un admin crea un cliente con un referente', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/clients', [
            'name' => 'Centro Mega',
            'status' => 'active',
            'contacts' => [['name' => 'Anna Rossi', 'email' => 'anna@centromega.it']],
        ])
        ->assertRedirect('/clients');

    $client = Client::firstWhere('name', 'Centro Mega');

    expect($client)->not->toBeNull()
        ->and($client->contacts[0]['name'])->toBe('Anna Rossi');
});

it('mette un cliente in pausa registrando la data', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $this->actingAs($admin)
        ->put("/clients/{$client->id}", ['name' => $client->name, 'status' => 'paused']);

    expect($client->fresh()->paused_at)->not->toBeNull();
});

it('un account manager vede solo i clienti assegnati', function () {
    $am = User::factory()->accountManager()->create();
    $mine = Client::factory()->count(2)->create();
    Client::factory()->count(3)->create();      // di altri

    $am->clients()->attach($mine->pluck('id'));

    $this->actingAs($am)
        ->get('/clients')
        ->assertInertia(fn ($page) => $page
            ->component('clients/index')
            ->has('clients', 2)
        );
});

it('un account manager non può creare clienti', function () {
    $am = User::factory()->accountManager()->create();

    $this->actingAs($am)->post('/clients', ['name' => 'X', 'status' => 'active'])
        ->assertForbidden();
});

it('il soft delete toglie il cliente dalla lista ma non dal database', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $this->actingAs($admin)->delete("/clients/{$client->id}");

    expect(Client::find($client->id))->toBeNull()
        ->and(Client::withTrashed()->find($client->id))->not->toBeNull();
});

it('rifiuta un logo più grande del limite configurato', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/clients', [
            'name' => 'Troppo Grande',
            'status' => 'active',
            'logo' => UploadedFile::fake()
                ->image('logo.jpg')->size(config('ped.logo_max_kb') + 1),
        ])
        ->assertSessionHasErrors('logo');
});

it('un admin crea un cliente con due referenti e un logo, poi lo modifica', function () {
    Storage::fake('public');

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/clients', [
            'name' => 'Nordica Coffee',
            'status' => 'active',
            'contacts' => [
                ['name' => 'Anna Rossi', 'email' => 'anna@nordica.it'],
                ['name' => 'Marco Bianchi', 'email' => 'marco@nordica.it'],
            ],
            'logo' => UploadedFile::fake()->image('logo.jpg'),
        ])
        ->assertRedirect('/clients');

    $client = Client::firstWhere('name', 'Nordica Coffee');

    expect($client->contacts)->toHaveCount(2)
        ->and($client->logo_path)->not->toBeNull();

    Storage::disk('public')->assertExists($client->logo_path);

    $this->actingAs($admin)
        ->post("/clients/{$client->id}", [
            '_method' => 'put',
            'name' => $client->name,
            'status' => 'active',
            'contacts' => [
                ['name' => 'Anna Rossi Aggiornata', 'email' => 'anna@nordica.it'],
                ['name' => 'Marco Bianchi', 'email' => 'marco@nordica.it'],
            ],
        ])
        ->assertRedirect('/clients');

    expect($client->fresh()->contacts[0]['name'])->toBe('Anna Rossi Aggiornata');
});
