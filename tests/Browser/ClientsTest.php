<?php

use App\Models\User;

it('crea un cliente dalla interfaccia e lo vede nella lista', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $page = visit('/clients');

    $page->click('a[href="/clients/create"]')
        ->type('name', 'Nordica Coffee')
        ->click('button[type="submit"]')
        ->assertSee('Nordica Coffee')
        ->assertNoJavascriptErrors();
});
