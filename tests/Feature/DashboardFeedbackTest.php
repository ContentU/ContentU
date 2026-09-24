<?php

use App\Models\Client;
use App\Models\ClientPublicLink;
use App\Models\Content;
use App\Models\Quarter;
use App\Models\ShootingSession;
use App\Models\TopicPreview;
use App\Models\User;
use App\Notifications\ClientActionTaken;
use App\Notifications\ClientShootingFeedback;
use App\Notifications\TopicPreviewResponded;

it('un commento del cliente su un contenuto crea una notifica per un admin non assegnato al cliente', function () {
    $client = Client::factory()->create();
    ClientPublicLink::factory()->for($client)->create();
    $quarter = Quarter::factory()->for($client)->create();
    $content = Content::factory()->for($quarter)->create();

    $unassignedAdmin = User::factory()->admin()->create();

    $clientUser = User::factory()->client()->create();
    $clientUser->clients()->attach($client);

    $this->actingAs($clientUser)
        ->post("/ped/{$client->slug}/contents/{$content->id}/comment", ['body' => 'Ci piace!'])
        ->assertRedirect();

    expect($unassignedAdmin->notifications()->where('type', ClientActionTaken::class)->count())->toBe(1);

    $this->actingAs($unassignedAdmin)
        ->get('/dashboard')
        ->assertInertia(fn ($page) => $page->has('clientFeedback', 1));
});

it('un commento del cliente sullo shooting crea una notifica per gli admin', function () {
    $client = Client::factory()->create();
    ClientPublicLink::factory()->for($client)->create();
    $session = ShootingSession::factory()->for($client)->create(['session_date' => today()->addWeek()]);

    $admin = User::factory()->admin()->create();

    $clientUser = User::factory()->client()->create();
    $clientUser->clients()->attach($client);

    $this->actingAs($clientUser)
        ->post("/ped/{$client->slug}/shooting/{$session->id}/comment", ['body' => 'Va bene la data'])
        ->assertRedirect();

    expect($admin->notifications()->where('type', ClientShootingFeedback::class)->count())->toBe(1);
});

it('respondToTopics avvisa anche gli admin non assegnati, non solo il team del cliente', function () {
    $client = Client::factory()->create();
    ClientPublicLink::factory()->for($client)->create();
    $quarter = Quarter::factory()->for($client)->create();
    $topicPreview = TopicPreview::factory()->for($quarter)->create();

    $unassignedAdmin = User::factory()->admin()->create();

    $clientUser = User::factory()->client()->create();
    $clientUser->clients()->attach($client);

    $this->actingAs($clientUser)
        ->post("/ped/{$client->slug}/topics/{$topicPreview->id}/respond", ['status' => 'approved'])
        ->assertRedirect();

    expect($unassignedAdmin->notifications()->where('type', TopicPreviewResponded::class)->count())->toBe(1);
});
