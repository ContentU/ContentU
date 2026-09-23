<?php

namespace App\Support;

use App\Models\ShootingSession;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class WorkloadCalculator
{
    /**
     * Giornate di shooting per persona nel mese, per ruolo.
     * Le assegnazioni "alternative" NON contano: non sono confermate.
     */
    public static function forMonth(CarbonImmutable $month): Collection
    {
        $start = $month->startOfMonth();
        $end = $month->endOfMonth();

        return User::whereIn('role', ['admin', 'account_manager', 'copywriter'])
            ->where('is_active', true)
            ->get()
            ->map(function (User $user) use ($start, $end) {
                $sessions = ShootingSession::whereBetween('session_date', [$start, $end])
                    ->whereHas('assignments', fn ($q) => $q
                        ->where('user_id', $user->id)
                        ->where('is_alternative', false))
                    ->with('assignments')
                    ->get();

                $byRole = $sessions
                    ->flatMap->assignments
                    ->where('user_id', $user->id)
                    ->where('is_alternative', false)
                    ->countBy('role');

                return [
                    'userId' => $user->id,
                    'name' => $user->name,
                    // Una persona impegnata su due ruoli nello stesso giorno
                    // conta una giornata sola.
                    'days' => $sessions->unique('session_date')->count(),
                    'byRole' => $byRole,
                    'overTarget' => $sessions->unique('session_date')->count()
                        > config('ped.shooting.target_days_per_month'),
                    'overMax' => $sessions->unique('session_date')->count()
                        > config('ped.shooting.max_days_per_month'),
                ];
            })
            ->sortByDesc('days')
            ->values();
    }
}
