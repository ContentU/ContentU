<?php

use App\Models\Content;
use App\Models\Quarter;
use App\Models\User;

it('apre il pannello di dettaglio e mostra la caption del contenuto di oggi', function () {
    $admin = User::factory()->admin()->create();
    $quarter = Quarter::factory()->create();

    Content::factory()->for($quarter)->create([
        'title' => 'Post di oggi',
        'caption' => 'Una caption di prova',
        'publish_at' => now(),
    ]);

    $this->actingAs($admin);

    $page = visit("/quarters/{$quarter->id}/feed");

    $page->click('button[aria-label="Post di oggi"]')
        ->assertSee('Una caption di prova')
        ->assertNoJavascriptErrors();
});

it('porta un contenuto in bozza in revisione dal pannello di dettaglio', function () {
    $admin = User::factory()->admin()->create();
    $quarter = Quarter::factory()->create();

    $content = Content::factory()->for($quarter)->create([
        'title' => 'Bozza da avanzare',
        'status' => 'draft',
        'publish_at' => now(),
    ]);

    $this->actingAs($admin);

    $page = visit("/quarters/{$quarter->id}/feed");

    $page->click('button[aria-label="Bozza da avanzare"]')
        ->assertSee('Porta in revisione')
        ->click('Porta in revisione')
        ->assertSee('In revisione')
        ->assertNoJavascriptErrors();

    expect($content->fresh()->status->value)->toBe('in_review');
});
