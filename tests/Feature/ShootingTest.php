<?php

use App\Enums\AlertType;
use App\Models\Alert;
use App\Models\Client;
use App\Models\ClientPublicLink;
use App\Models\Quarter;
use App\Models\ShootingSession;
use App\Models\ShootingTarget;
use App\Models\User;
use App\Support\WorkloadCalculator;
use Carbon\CarbonImmutable;

it('registra un target trimestrale con sessioni potenziali', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    $quarter = Quarter::factory()->for($client)->create(['label' => 'Q4 2026']);

    $this->actingAs($admin)->post('/shooting/targets', [
        'client_id' => $client->id,
        'quarter_id' => $quarter->id,
        'ideal_sessions' => 4,
        'planned_sessions' => 3,
        'potential_sessions' => 1,
        'weight' => 'M',
    ]);

    $target = ShootingTarget::first();

    expect($target->period_label)->toBe('Q4 2026')
        ->and($target->ideal_sessions)->toBe(4)
        ->and($target->potential_sessions)->toBe(1);
});

it('calcola il carico contando una sola giornata per persona su due ruoli', function () {
    $user = User::factory()->accountManager()->create();
    $session = ShootingSession::factory()->create(['session_date' => today()]);

    $session->assignments()->create(['user_id' => $user->id, 'role' => 'photo']);
    $session->assignments()->create(['user_id' => $user->id, 'role' => 'coordination']);

    $row = WorkloadCalculator::forMonth(CarbonImmutable::now())
        ->firstWhere('userId', $user->id);

    expect($row['days'])->toBe(1);
});

it('non conta le assegnazioni alternative nel carico', function () {
    $user = User::factory()->accountManager()->create();
    $session = ShootingSession::factory()->create(['session_date' => today()]);

    $session->assignments()->create([
        'user_id' => $user->id, 'role' => 'video', 'is_alternative' => true,
    ]);

    $row = WorkloadCalculator::forMonth(CarbonImmutable::now())
        ->firstWhere('userId', $user->id);

    expect($row['days'])->toBe(0);
});

it('genera un alert quando una persona supera il tetto mensile', function () {
    config(['ped.shooting.max_days_per_month' => 4]);

    Client::factory()->create(['status' => 'active']);
    $user = User::factory()->accountManager()->create(['name' => 'Giorgio']);

    foreach (range(1, 5) as $day) {
        $session = ShootingSession::factory()->create([
            'session_date' => today()->startOfMonth()->addDays($day),
        ]);
        $session->assignments()->create(['user_id' => $user->id, 'role' => 'video']);
    }

    $this->artisan('ped:check-alerts');

    $alert = Alert::open()
        ->where('type', AlertType::ShootingOverload->value)->first();

    expect($alert)->not->toBeNull()
        ->and($alert->message)->toContain('Giorgio')
        ->and($alert->message)->toContain('5 giornate');
});

it('SICUREZZA — la vista cliente non espone mai nomi del team né note interne', function () {
    $client = Client::factory()->create();
    $link = ClientPublicLink::factory()->for($client)->create();

    $giorgio = User::factory()->accountManager()->create(['name' => 'Giorgio Rossi']);

    $session = ShootingSession::factory()->for($client)->create([
        'session_date' => today()->addWeek(),
        'type' => 'photo_video',
        'internal_note' => 'CRITICITA INTERNA: il cliente non conferma mai in tempo',
        'checkpoint_note' => 'CHECKPOINT: verificare la library esistente',
        'is_tentative' => true,
    ]);
    $session->assignments()->create(['user_id' => $giorgio->id, 'role' => 'video']);

    $user = User::factory()->client()->create();
    $user->clients()->attach($client);

    // Niente header X-Inertia-Version fittizio: farebbe rispondere 409 con
    // corpo vuoto e il test passerebbe sempre, a vuoto, senza controllare nulla.
    $json = $this->actingAs($user)
        ->get("/ped/{$client->slug}/shooting")
        ->getContent();

    // Decisione B: solo data e tipo.
    expect($json)->toContain('Foto + Video')
        ->and($json)->not->toContain('Giorgio Rossi')
        ->and($json)->not->toContain('CRITICITA INTERNA')
        ->and($json)->not->toContain('CHECKPOINT')
        ->and($json)->not->toContain('is_tentative')
        ->and($json)->not->toContain('internalNote');
});

it('SICUREZZA — un cliente non vede le sessioni di un altro cliente', function () {
    $a = Client::factory()->create();
    $b = Client::factory()->create();

    $linkA = ClientPublicLink::factory()->for($a)->create();
    ShootingSession::factory()->for($b)->create(['session_date' => today()->addWeek()]);

    $userA = User::factory()->client()->create();
    $userA->clients()->attach($a);

    $this->actingAs($userA)->get("/ped/{$a->slug}/shooting")
        ->assertInertia(fn ($page) => $page->has('sessions', 0));
});

it('la vista interna mostra le alternative senza nasconderle', function () {
    $admin = User::factory()->admin()->create();
    $session = ShootingSession::factory()->create(['session_date' => today()->addWeek()]);

    $confermato = User::factory()->accountManager()->create(['name' => 'Giorgio']);
    $alternativo = User::factory()->accountManager()->create(['name' => 'Veronica']);

    $session->assignments()->create(['user_id' => $confermato->id, 'role' => 'video']);
    $session->assignments()->create([
        'user_id' => $alternativo->id, 'role' => 'video', 'is_alternative' => true,
    ]);

    $this->actingAs($admin)->get('/shooting')
        ->assertSee('Giorgio')
        ->assertSee('Veronica');
});
