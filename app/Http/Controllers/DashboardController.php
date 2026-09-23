<?php

namespace App\Http\Controllers;

use App\Enums\ClientStatus;
use App\Models\Alert;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $status = $request->string('status')->toString();

        // Stessa regola di §3.1: l'admin vede tutto, gli altri solo i propri clienti.
        $baseQuery = fn () => $user->isAdmin() ? Client::query() : $user->clients();

        $clients = $baseQuery()
            ->with(['teamMembers:id,name', 'quarters'])
            ->when($status, fn ($q, $s) => $q->where('status', $s))
            ->when(! $status, fn ($q) => $q->where('status', '!=', ClientStatus::Archived->value))
            ->orderBy('name')
            ->get();

        // Gli archiviati restano nascosti di default: contati a parte per il link in fondo.
        $archivedCount = $baseQuery()->where('status', ClientStatus::Archived->value)->count();

        return Inertia::render('dashboard', [
            'stats' => [
                'activeClients' => $clients->where('status', ClientStatus::Active)->count(),
                'openAlerts' => Alert::open()->whereIn('client_id', $clients->pluck('id'))->count(),
                'teamSize' => User::whereIn('role', ['admin', 'account_manager', 'copywriter'])
                    ->where('is_active', true)->count(),
                'currentQuarter' => 'Q'.ceil(today()->month / 3).' '.today()->year,
            ],
            'clients' => $clients->map(function (Client $client) {
                $current = $client->quarters->first(fn ($q) => $q->isCurrent());

                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'initials' => $client->initials(),
                    'status' => $client->status->value,
                    'pausedAt' => $client->paused_at?->format('j M'),
                    'health' => $current?->healthPercentage(),
                    'alerts' => Alert::open()->where('client_id', $client->id)
                        ->latest()->limit(2)->pluck('message'),
                    'team' => $client->teamMembers->map(fn (User $u) => [
                        'id' => $u->id, 'name' => $u->name, 'initials' => $u->initials(),
                    ]),
                ];
            }),
            'archivedCount' => $archivedCount,
            'filters' => ['status' => $status],
        ]);
    }
}
