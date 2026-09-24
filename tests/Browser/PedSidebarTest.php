<?php

use App\Models\Client;
use App\Models\ClientPublicLink;
use App\Models\User;

it('l\'admin vede la sidebar del PED con il gruppo Admin e torna alla dashboard', function () {
    $client = Client::factory()->create(['name' => 'Canalotto Farm']);
    ClientPublicLink::factory()->for($client)->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $page = visit("/ped/{$client->slug}/argomenti");

    $page->assertSee('Canalotto Farm')
        ->assertSee('Argomenti')
        ->assertSee('Feed')
        ->assertSee('Shooting')
        ->assertSee('Torna alla dashboard')
        ->click('Torna alla dashboard')
        ->assertPathIs('/dashboard')
        ->assertNoJavascriptErrors();
});

it('il cliente vede solo le 3 voci del PED nella sidebar, senza il gruppo Admin', function () {
    $client = Client::factory()->create(['name' => 'Canalotto Farm']);
    ClientPublicLink::factory()->for($client)->create();

    $user = User::factory()->client()->create();
    $user->clients()->attach($client);

    $this->actingAs($user);

    $page = visit("/ped/{$client->slug}/argomenti");

    $page->assertSee('Argomenti')
        ->assertSee('Feed')
        ->assertSee('Shooting')
        ->assertDontSee('Torna alla dashboard')
        ->assertNoJavascriptErrors();
});
