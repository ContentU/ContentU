<?php

namespace App\Http\Controllers;

use App\Enums\QuarterStatus;
use App\Models\Client;
use App\Models\Quarter;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class QuarterController extends Controller
{
    use AuthorizesRequests;

    public function index(Client $client)
    {
        $this->authorize('view', $client);

        return Inertia::render('quarters/index', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
            ],
            'quarters' => $client->quarters()
                ->orderByDesc('year')
                ->orderByDesc('quarter_number')
                ->get()
                ->map(fn (Quarter $q) => [
                    'id' => $q->id,
                    'label' => $q->label,
                    'status' => $q->status->value,
                    'statusLabel' => $q->status->label(),
                ]),
        ]);
    }

    public function store(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'quarter_number' => ['required', 'integer', 'min:1', 'max:4'],
        ]);

        // Il vincolo unique in DB è la garanzia; qui diamo un errore leggibile.
        $exists = $client->quarters()
            ->where('year', $data['year'])
            ->where('quarter_number', $data['quarter_number'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'quarter_number' => 'Questo trimestre esiste già per il cliente.',
            ]);
        }

        $client->quarters()->create([
            ...$data,
            ...Quarter::deriveDates($data['year'], $data['quarter_number']),
            'status' => QuarterStatus::Draft->value,
        ]);

        Inertia::flash('message', 'Trimestre creato.');

        return back();
    }

    public function show(Quarter $quarter)
    {
        $this->authorize('view', $quarter->client);

        return Inertia::render('quarters/show', [
            'client' => [
                'id' => $quarter->client->id,
                'name' => $quarter->client->name,
            ],
            'quarter' => [
                'id' => $quarter->id,
                'label' => $quarter->label,
                'status' => $quarter->status->value,
                'statusLabel' => $quarter->status->label(),
            ],
            'allowedTransitions' => collect($quarter->status->allowedNext())
                ->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
        ]);
    }

    public function updateStatus(Request $request, Quarter $quarter)
    {
        $this->authorize('update', $quarter->client);

        $data = $request->validate([
            'status' => ['required', Rule::enum(QuarterStatus::class)],
        ]);

        $target = QuarterStatus::from($data['status']);

        // Guardia lato SERVER: non fidarsi mai del front-end sulle transizioni.
        if (! $quarter->status->canTransitionTo($target)) {
            return back()->withErrors([
                'status' => "Non si può passare da «{$quarter->status->label()}» a «{$target->label()}».",
            ]);
        }

        $wasDraft = $quarter->status === QuarterStatus::Draft;

        $quarter->update(['status' => $target->value]);

        // Notifica al cliente quando il PED entra in revisione (§3.5 S, inclusa in v1).
        // Il meccanismo di invio arriva nella Fase 07: qui lasciamo l'aggancio.
        if ($wasDraft && $target === QuarterStatus::InReview) {
            // TODO Fase 07: notificare gli utenti-cliente con il link pubblico.
        }

        Inertia::flash('message', 'Stato del trimestre aggiornato.');

        return back();
    }
}
