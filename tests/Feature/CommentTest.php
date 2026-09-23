<?php

use App\Models\Client;
use App\Models\ClientPublicLink;
use App\Models\Comment;
use App\Models\Content;
use App\Models\Quarter;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

it('etichetta correttamente un commento del cliente', function () {
    Notification::fake();

    $client = Client::factory()->create();
    $link = ClientPublicLink::factory()->for($client)->create();
    $quarter = Quarter::factory()->for($client)->create();
    $content = Content::factory()->for($quarter)->approved()->create();

    $user = User::factory()->client()->create();
    $user->clients()->attach($client);

    $this->actingAs($user)
        ->post("/ped/{$link->token}/contents/{$content->id}/comment", [
            'body' => 'La foto è troppo scura.',
        ]);

    $comment = $content->comments()->first();

    expect($comment->author_role)->toBe('client')
        ->and($comment->authorLabel())->toBe('Cliente')
        ->and($content->fresh()->status->value)->toBe('needs_changes');
});

it('un commento del team non cambia lo stato del contenuto', function () {
    Notification::fake();

    $client = Client::factory()->create();
    $am = User::factory()->accountManager()->create();
    $client->users()->attach($am);

    $quarter = Quarter::factory()->for($client)->create();
    $content = Content::factory()->for($quarter)->approved()->create();

    $this->actingAs($am)->post("/contents/{$content->id}/comments", ['body' => 'Ok per me.']);

    expect($content->fresh()->status->value)->toBe('approved');
});

it('mantiene i commenti in ordine cronologico', function () {
    $content = Content::factory()->create();

    $first = Comment::factory()->for($content)->fromClient()->create(['created_at' => now()->subHour()]);
    $second = Comment::factory()->for($content)->create(['created_at' => now()]);

    expect($content->comments()->orderBy('created_at')->pluck('id')->all())
        ->toBe([$first->id, $second->id]);
});

it('riporta in bozza un contenuto in richiesta modifica', function () {
    $admin = User::factory()->admin()->create();
    $content = Content::factory()->create(['status' => 'needs_changes']);

    $this->actingAs($admin)
        ->patch("/contents/{$content->id}/status", ['status' => 'draft'])
        ->assertSessionHasNoErrors();

    expect($content->fresh()->status->value)->toBe('draft');
});

it('non permette di saltare da richiesta modifica ad approvato', function () {
    $admin = User::factory()->admin()->create();
    $content = Content::factory()->create(['status' => 'needs_changes']);

    $this->actingAs($admin)
        ->patch("/contents/{$content->id}/status", ['status' => 'approved'])
        ->assertSessionHasErrors('status');
});

it('un account manager vede solo i clienti assegnati', function () {
    $am = User::factory()->accountManager()->create();
    $mine = Client::factory()->count(2)->create();
    Client::factory()->count(3)->create();
    $am->clients()->attach($mine->pluck('id'));

    $this->actingAs($am)->get('/clients')
        ->assertInertia(fn ($page) => $page->has('clients', 2));
});

it('un copywriter non può approvare né pubblicare', function () {
    $copy = User::factory()->copywriter()->create();
    $client = Client::factory()->create();
    $copy->clients()->attach($client);

    $quarter = Quarter::factory()->for($client)->create();
    $content = Content::factory()->for($quarter)->create(['status' => 'in_review']);

    $this->actingAs($copy)
        ->patch("/contents/{$content->id}/status", ['status' => 'approved'])
        ->assertForbidden();

    $this->actingAs($copy)->get('/clients')
        ->assertInertia(fn ($page) => $page->where('auth.can.approve', false));
});

it('un cliente non può mai modificare un contenuto', function () {
    $client = Client::factory()->create();
    $quarter = Quarter::factory()->for($client)->create();
    $content = Content::factory()->for($quarter)->create();

    $user = User::factory()->client()->create();
    $user->clients()->attach($client);

    $this->actingAs($user)
        ->put("/contents/{$content->id}", ['title' => 'Testo cambiato dal cliente'])
        ->assertForbidden();
});
