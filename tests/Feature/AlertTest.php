<?php

use App\Enums\AlertType;
use App\Models\Alert;
use App\Models\Client;
use App\Models\Content;
use App\Models\Quarter;
use App\Models\Tag;
use App\Models\User;
use App\Notifications\AlertRaised;

it('genera un alert per un contenuto senza risorsa in scadenza', function () {
    $client = Client::factory()->create(['status' => 'active']);
    $quarter = Quarter::factory()->for($client)->create();

    Content::factory()->for($quarter)->create([
        'title' => 'Post listino autunnale',
        'resource_url' => null,
        'publish_at' => now()->addDays(5),
        'status' => 'approved',
    ]);

    $this->artisan('ped:check-alerts')->assertSuccessful();

    $alert = Alert::open()->where('type', AlertType::MissingResource->value)->first();

    expect($alert)->not->toBeNull()
        ->and($alert->message)->toContain('Post listino autunnale');
});

it('non genera alert per contenuti oltre la soglia configurata', function () {
    config(['ped.alerts.missing_resource_days' => 7]);

    $client = Client::factory()->create(['status' => 'active']);
    $quarter = Quarter::factory()->for($client)->create();

    // Caption e tag presenti: isola la condizione sotto test dalla M "caption o tag mancanti".
    $content = Content::factory()->for($quarter)->create([
        'resource_url' => null,
        'publish_at' => now()->addDays(30),
        'status' => 'approved',
    ]);
    $content->tags()->attach(Tag::factory()->create());

    $this->artisan('ped:check-alerts');

    expect(Alert::count())->toBe(0);
});

it('non duplica lo stesso alert eseguendo il comando due volte', function () {
    $client = Client::factory()->create(['status' => 'active']);
    $quarter = Quarter::factory()->for($client)->create();

    Content::factory()->for($quarter)->create([
        'resource_url' => null, 'publish_at' => now()->addDays(3), 'status' => 'approved',
    ]);

    $this->artisan('ped:check-alerts');
    $this->artisan('ped:check-alerts');

    expect(Alert::where('type', AlertType::MissingResource->value)->count())->toBe(1);
});

it('segnala un trimestre in scadenza senza il successivo', function () {
    $client = Client::factory()->create(['status' => 'active']);

    Quarter::factory()->for($client)->create([
        'year' => 2026, 'quarter_number' => 4, 'status' => 'approved',
        'label' => 'Q4 2026',
        'starts_on' => '2026-10-01', 'ends_on' => today()->addDays(10)->toDateString(),
    ]);

    $this->artisan('ped:check-alerts');

    $alert = Alert::open()->where('type', AlertType::NextQuarterMissing->value)->first();

    expect($alert)->not->toBeNull()
        ->and($alert->message)->toContain('Q1 2027');
});

it('chiude un alert quando la condizione non è più vera', function () {
    $client = Client::factory()->create(['status' => 'active']);
    // Trimestre fissato a un periodo non corrente: altrimenti la M
    // "contenuti in esaurimento" scatterebbe in modo intermittente a seconda
    // del trimestre casuale generato dalla factory, rendendo il test flaky.
    $quarter = Quarter::factory()->for($client)->period(2030, 1)->create();

    $content = Content::factory()->for($quarter)->create([
        'resource_url' => null, 'publish_at' => now()->addDays(3), 'status' => 'approved',
    ]);
    $content->tags()->attach(Tag::factory()->create());

    $this->artisan('ped:check-alerts');
    expect(Alert::open()->count())->toBe(1);

    $content->update(['resource_url' => 'https://drive.example.com/file']);

    $this->artisan('ped:check-alerts');
    expect(Alert::open()->where('type', AlertType::MissingResource->value)->count())->toBe(0);
});

it('mostra il conteggio delle notifiche non lette e lo azzera', function () {
    $admin = User::factory()->admin()->create();

    $admin->notify(new AlertRaised(Alert::factory()->create()));

    $this->actingAs($admin)->get('/dashboard')
        ->assertInertia(fn ($page) => $page->reloadOnly(
            'notifications',
            fn ($reloaded) => $reloaded->where('notifications.unreadCount', 1),
        ));

    $this->actingAs($admin)->patch('/notifications/read-all');

    expect($admin->fresh()->unreadNotifications()->count())->toBe(0);
});

it('non permette di marcare come lette le notifiche di un altro utente', function () {
    $mine = User::factory()->admin()->create();
    $theirs = User::factory()->admin()->create();

    $theirs->notify(new AlertRaised(Alert::factory()->create()));
    $id = $theirs->notifications()->first()->id;

    $this->actingAs($mine)->patch("/notifications/{$id}/read")->assertNotFound();
});
