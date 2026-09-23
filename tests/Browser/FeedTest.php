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
