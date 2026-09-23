<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Quarter;
use App\Models\ShootingSession;
use App\Models\ShootingTarget;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

/**
 * Dati di test per il modulo shooting (target, sessioni, assegnazioni) sui
 * clienti/trimestri creati da DemoSeeder. Da eseguire solo in local/testing.
 */
class ShootingDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            $this->command->warn('ShootingDemoSeeder saltato: disponibile solo in local/testing.');

            return;
        }

        $crew = User::whereIn('role', ['admin', 'account_manager', 'copywriter'])
            ->where('is_active', true)
            ->get();

        if ($crew->isEmpty()) {
            $this->command->warn('ShootingDemoSeeder saltato: nessun utente team disponibile.');

            return;
        }

        Client::with('quarters')->get()->each(function (Client $client) use ($crew) {
            $client->quarters->each(fn (Quarter $quarter) => $this->seedTarget($client, $quarter));

            $sessionsCount = fake()->numberBetween(2, 4);
            for ($n = 0; $n < $sessionsCount; $n++) {
                $this->seedSession($client, $crew);
            }
        });
    }

    /** ShootingTargetFactory crea sempre un trimestre fittizio: qui ne abbiamo già uno reale. */
    private function seedTarget(Client $client, Quarter $quarter): void
    {
        $ideal = fake()->numberBetween(2, 6);
        $planned = fake()->numberBetween(0, $ideal);

        ShootingTarget::create([
            'client_id' => $client->id,
            'quarter_id' => $quarter->id,
            'period_label' => $quarter->label,
            'ideal_sessions' => $ideal,
            'planned_sessions' => $planned,
            'potential_sessions' => fake()->numberBetween(0, $ideal - $planned),
            'weight' => fake()->randomElement(['S', 'M', 'L']),
        ]);
    }

    /**
     * @param  Collection<int, User>  $crew
     */
    private function seedSession(Client $client, Collection $crew): void
    {
        $session = ShootingSession::factory()->create([
            'client_id' => $client->id,
            'is_tentative' => fake()->boolean(20),
            'checkpoint_required' => fake()->boolean(30),
        ]);

        // Utenti distinti per assegnazione: lo stesso utente con lo stesso ruolo
        // sulla stessa sessione violerebbe il vincolo unique.
        $wantsAlternate = $crew->count() >= 3 && fake()->boolean(25);
        $assignees = $crew->random(min($wantsAlternate ? 3 : 2, $crew->count()))->values();

        $roles = ['coordination', fake()->randomElement(['photo', 'video'])];
        if ($assignees->count() === 3) {
            $roles[] = fake()->randomElement(['photo', 'video']);
        }

        $assignees->each(function (User $user, int $i) {
            $session->assignments()->create([
                'user_id' => $user->id,
                'role' => $roles[$i],
                'is_alternative' => $i === 2,
            ]);
        });
    }
}
