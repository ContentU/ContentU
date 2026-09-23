<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Quarter;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Dati di test per lo sviluppo locale: clienti, team assegnato, utenti-cliente e trimestri.
 * Tutte le password sono 'password'. Da eseguire solo in local/testing.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            $this->command?->warn('DemoSeeder saltato: disponibile solo in local/testing.');

            return;
        }

        // Team aggiuntivo oltre agli utenti fissi di UserSeeder.
        User::factory()->accountManager()->count(2)->create();
        User::factory()->copywriter()->count(3)->create();
        User::factory()->copywriter()->inactive()->create();

        $accountManagers = User::where('role', UserRole::AccountManager)->where('is_active', true)->get();
        $copywriters = User::where('role', UserRole::Copywriter)->where('is_active', true)->get();

        $clients = collect()
            ->merge(Client::factory()->detailed()->count(5)->create())
            ->merge(Client::factory()->detailed()->paused()->count(1)->create())
            ->merge(Client::factory()->detailed()->archived()->count(1)->create());

        // Un cliente in soft delete, per verificare che resti fuori dalle liste.
        Client::factory()->create()->delete();

        foreach ($clients as $client) {
            $client->users()->attach([
                $accountManagers->random()->id,
                ...$copywriters->random(2)->pluck('id'),
            ]);

            $client->users()->attach(
                User::factory()->client()->create(['name' => $client->contacts[0]['name']])->id,
            );

            $this->seedQuarters($client);
        }

        // Gli utenti fissi vedono sempre almeno un cliente.
        $first = $clients->first();
        $first->users()->syncWithoutDetaching(
            User::whereIn('email', ['am@example.com', 'copy@example.com'])->pluck('id'),
        );
    }

    /** Trimestri dell'anno corrente, con stato coerente rispetto a oggi. */
    private function seedQuarters(Client $client): void
    {
        $year = now()->year;
        $current = now()->quarter;

        foreach (range(1, 4) as $n) {
            $factory = Quarter::factory()->for($client)->period($year, $n);

            $factory = match (true) {
                $n < $current => $factory->closed(),
                $n === $current => $factory->approved(),
                $n === $current + 1 => fake()->boolean() ? $factory->inReview() : $factory,
                default => $factory,
            };

            $factory->create();
        }
    }
}
