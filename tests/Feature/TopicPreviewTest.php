<?php

use App\Models\Client;
use App\Models\ClientPublicLink;
use App\Models\Quarter;
use App\Models\TopicPreview;
use App\Models\TopicPreviewItem;
use App\Models\User;
use App\Notifications\TopicPreviewResponded;
use App\Support\TopicSynthesis;
use Illuminate\Support\Facades\Notification;

it('mostra al cliente i mesi con i badge di stato corretti', function () {
    $client = Client::factory()->create();
    $link = ClientPublicLink::factory()->for($client)->create();
    $quarter = Quarter::factory()->for($client)->create();

    $ottobre = TopicPreview::factory()->for($quarter)->create([
        'month_label' => 'Ottobre', 'month_order' => 1, 'status' => 'ready',
    ]);
    TopicPreview::factory()->for($quarter)->create([
        'month_label' => 'Novembre', 'month_order' => 2, 'status' => 'draft',
    ]);

    TopicPreviewItem::factory()->for($ottobre, 'preview')->create([
        'title' => 'È arrivata la guava biologica siciliana',
        'period_label' => 'Inizio ottobre',
    ]);

    $user = User::factory()->client()->create();
    $user->clients()->attach($client);

    $this->actingAs($user)->get("/ped/{$link->token}/argomenti")
        ->assertInertia(fn ($page) => $page
            ->component('public-ped/topic-preview')
            ->has('months', 2)
            ->where('months.0.status', 'ready')
            ->where('months.1.status', 'draft')
            ->where('months.0.items.0.periodLabel', 'Inizio ottobre')
        );
});

it('calcola i filoni ricorrenti dai temi', function () {
    $quarter = Quarter::factory()->create();
    $preview = TopicPreview::factory()->for($quarter)->create();

    foreach (['Agrumi — varietà', 'Agrumi — raccolta', 'Olio novello'] as $theme) {
        TopicPreviewItem::factory()->for($preview, 'preview')->create(['theme' => $theme]);
    }

    $themes = TopicSynthesis::recurringThemes($quarter);

    expect($themes->first()['theme'])->toBe('Agrumi')
        ->and($themes->first()['count'])->toBe(2);
});

it('registra una approvazione con modifiche e avvisa il team', function () {
    Notification::fake();

    $client = Client::factory()->create();
    $am = User::factory()->accountManager()->create();
    $client->users()->attach($am);

    $link = ClientPublicLink::factory()->for($client)->create();
    $quarter = Quarter::factory()->for($client)->create();
    $preview = TopicPreview::factory()->for($quarter)->create();

    $user = User::factory()->client()->create();
    $user->clients()->attach($client);

    $this->actingAs($user)
        ->post("/ped/{$link->token}/topics/{$preview->id}/respond", [
            'status' => 'approved_with_notes',
            'comment' => 'Va bene, ma spostiamo il tema olio a novembre.',
        ]);

    expect($preview->latestApproval->status)->toBe('approved_with_notes');
    Notification::assertSentTo($am, TopicPreviewResponded::class);
});

it('richiede un commento quando la risposta non è una approvazione piena', function () {
    $client = Client::factory()->create();
    $link = ClientPublicLink::factory()->for($client)->create();
    $quarter = Quarter::factory()->for($client)->create();
    $preview = TopicPreview::factory()->for($quarter)->create();

    $user = User::factory()->client()->create();
    $user->clients()->attach($client);

    $this->actingAs($user)
        ->post("/ped/{$link->token}/topics/{$preview->id}/respond", ['status' => 'revise'])
        ->assertSessionHasErrors('comment');
});

it('trasforma un tema approvato in un contenuto reale', function () {
    $admin = User::factory()->admin()->create();
    $item = TopicPreviewItem::factory()->create(['title' => 'Regalare la Sicilia']);

    $this->actingAs($admin)->post("/topic-items/{$item->id}/promote");

    $item->refresh();

    expect($item->content_id)->not->toBeNull()
        ->and($item->content->title)->toBe('Regalare la Sicilia')
        ->and($item->content->status->value)->toBe('draft');
});

it('non trasforma due volte lo stesso tema', function () {
    $admin = User::factory()->admin()->create();
    $item = TopicPreviewItem::factory()->create();

    $this->actingAs($admin)->post("/topic-items/{$item->id}/promote");
    $this->actingAs($admin)->post("/topic-items/{$item->id}/promote")->assertStatus(409);
});

it('non espone la pre-verifica di un altro cliente', function () {
    $clientA = Client::factory()->create();
    $clientB = Client::factory()->create();

    $linkA = ClientPublicLink::factory()->for($clientA)->create();
    TopicPreview::factory()->for(Quarter::factory()->for($clientB)->create())->create([
        'note' => 'RISERVATO CLIENTE B',
    ]);

    $userA = User::factory()->client()->create();
    $userA->clients()->attach($clientA);

    $this->actingAs($userA)->get("/ped/{$linkA->token}/argomenti")
        ->assertDontSee('RISERVATO CLIENTE B');
});
