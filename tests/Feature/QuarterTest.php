<?php

use App\Models\Client;
use App\Models\Quarter;
use App\Models\User;

it('crea un trimestre derivando label e date', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $this->actingAs($admin)
        ->post("/clients/{$client->id}/quarters", ['year' => 2027, 'quarter_number' => 1]);

    $quarter = $client->quarters()->first();

    expect($quarter->label)->toBe('Q1 2027')
        ->and($quarter->starts_on->toDateString())->toBe('2027-01-01')
        ->and($quarter->ends_on->toDateString())->toBe('2027-03-31')
        ->and($quarter->status->value)->toBe('draft');
});

it('rifiuta un trimestre duplicato per lo stesso cliente', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();
    Quarter::factory()->for($client)->create(['year' => 2027, 'quarter_number' => 1]);

    $this->actingAs($admin)
        ->post("/clients/{$client->id}/quarters", ['year' => 2027, 'quarter_number' => 1])
        ->assertSessionHasErrors('quarter_number');

    expect($client->quarters()->count())->toBe(1);
});

it('consente la transizione bozza → in approvazione', function () {
    $admin = User::factory()->admin()->create();
    $quarter = Quarter::factory()->create(['status' => 'draft']);

    $this->actingAs($admin)
        ->patch("/quarters/{$quarter->id}/status", ['status' => 'in_review'])
        ->assertSessionHasNoErrors();

    expect($quarter->fresh()->status->value)->toBe('in_review');
});

it('rifiuta il salto da bozza ad approvato con un messaggio leggibile', function () {
    $admin = User::factory()->admin()->create();
    $quarter = Quarter::factory()->create(['status' => 'draft']);

    $this->actingAs($admin)
        ->patch("/quarters/{$quarter->id}/status", ['status' => 'approved'])
        ->assertSessionHasErrors('status');

    expect($quarter->fresh()->status->value)->toBe('draft');
});

it('non consente alcuna transizione da un trimestre concluso', function () {
    $admin = User::factory()->admin()->create();
    $quarter = Quarter::factory()->create(['status' => 'closed']);

    $this->actingAs($admin)
        ->patch("/quarters/{$quarter->id}/status", ['status' => 'approved'])
        ->assertSessionHasErrors('status');
});
