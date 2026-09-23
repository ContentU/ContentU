<?php

use App\Models\Client;
use App\Models\ClientPublicLink;
use App\Models\Content;
use App\Models\Quarter;
use App\Models\ShootingSession;
use App\Models\User;

/** Chiavi e valori che non devono MAI comparire in una risposta pubblica. */
const FORBIDDEN_KEYS = [
    'internalNotes', 'internal_notes',
    'shootingNotes', 'shooting_notes',
    'internalNote', 'internal_note',
    'checkpointNote', 'checkpoint_note',
    'toneOfVoice', 'tone_of_voice',
    'isTentative', 'is_tentative',
    'assignments', 'teamMembers', 'workload', 'alerts',
];

it('AUDIT — nessuna rotta pubblica espone chiavi riservate', function () {
    $client = Client::factory()->create([
        'internal_notes' => 'riservato',
        'shooting_notes' => 'riservato',
        'tone_of_voice' => 'riservato',
    ]);

    $link = ClientPublicLink::factory()->for($client)->create();
    $quarter = Quarter::factory()->for($client)->create();

    Content::factory()->for($quarter)->approved()->create();
    ShootingSession::factory()->for($client)->create([
        'internal_note' => 'riservato', 'checkpoint_note' => 'riservato',
    ]);

    $user = User::factory()->client()->create();
    $user->clients()->attach($client);

    $routes = ['', '/argomenti', '/feed', '/shooting'];

    foreach ($routes as $suffix) {
        // Il payload va ispezionato nella pagina completa (data-page incorporato):
        // niente header X-Inertia-Version fittizio, che farebbe rispondere 409
        // con corpo vuoto e renderebbe il controllo un falso positivo.
        $json = $this->actingAs($user)
            ->get("/ped/{$link->token}{$suffix}")
            ->getContent();

        foreach (FORBIDDEN_KEYS as $key) {
            expect($json)->not->toContain($key,
                "La rotta /ped/{token}{$suffix} espone la chiave riservata «{$key}».");
        }
    }
});
