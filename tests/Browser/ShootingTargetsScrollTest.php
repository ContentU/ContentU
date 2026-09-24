<?php

use App\Models\Client;
use App\Models\Quarter;
use App\Models\ShootingTarget;
use App\Models\User;

it('mostra al massimo 5 righe di target e scorre internamente oltre', function () {
    $admin = User::factory()->admin()->create();

    foreach (range(1, 7) as $i) {
        $client = Client::factory()->create(['name' => "Cliente $i"]);
        $quarter = Quarter::factory()->for($client)->create();
        ShootingTarget::factory()->for($client)->for($quarter)->create();
    }

    $this->actingAs($admin);

    $page = visit('/shooting');

    $page->assertSee('Cliente 1')
        ->assertNoJavascriptErrors();

    $overflow = $page->script(
        <<<'JS'
        (() => {
            const el = document.querySelector('[aria-label="Elenco target clienti"]');
            return el.scrollHeight > el.clientHeight;
        })()
        JS,
    );

    expect($overflow)->toBeTrue();
});

it('non scorre quando i target sono 5 o meno', function () {
    $admin = User::factory()->admin()->create();

    foreach (range(1, 3) as $i) {
        $client = Client::factory()->create(['name' => "Cliente $i"]);
        $quarter = Quarter::factory()->for($client)->create();
        ShootingTarget::factory()->for($client)->for($quarter)->create();
    }

    $this->actingAs($admin);

    $page = visit('/shooting');

    $page->assertSee('Cliente 1')
        ->assertNoJavascriptErrors();

    $overflow = $page->script(
        <<<'JS'
        (() => {
            const el = document.querySelector('[aria-label="Elenco target clienti"]');
            return el.scrollHeight > el.clientHeight;
        })()
        JS,
    );

    expect($overflow)->toBeFalse();
});
