<?php

use App\Models\Client;
use App\Models\ClientPublicLink;
use App\Models\Quarter;
use App\Models\TopicPreview;
use App\Models\TopicPreviewItem;
use App\Models\User;

it('l\'admin modifica un argomento dal PED e vede il nuovo titolo', function () {
    $client = Client::factory()->create();
    ClientPublicLink::factory()->for($client)->create();
    $quarter = Quarter::factory()->for($client)->create();
    $month = TopicPreview::factory()->for($quarter)->create(['month_label' => 'Ottobre']);
    $item = TopicPreviewItem::factory()->for($month, 'preview')->create([
        'title' => 'Titolo originale',
    ]);

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $page = visit("/ped/{$client->slug}/argomenti");

    $page->click('button[aria-label="Modifica argomento"]')
        ->clear('title')
        ->type('title', 'Titolo aggiornato')
        ->click('Salva')
        ->assertSee('Titolo aggiornato')
        ->assertNoJavascriptErrors();

    expect($item->fresh()->title)->toBe('Titolo aggiornato');
});
