<?php

namespace App\Http\Controllers;

use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\User;
use App\Support\ArtifactPromptBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Client::class);

        $user = $request->user();

        // Un account manager o copywriter vede solo i propri clienti.
        // Filtro lato QUERY, non lato front-end: i dati non devono nemmeno partire.
        $query = $user->isAdmin() ? Client::query() : $user->clients();

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        return Inertia::render('clients/index', [
            'clients' => $query->with('teamMembers:id,name')
                ->orderBy('name')
                ->get()
                ->map(fn (Client $c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'initials' => $c->initials(),
                    'status' => $c->status->value,
                    'pausedAt' => $c->paused_at?->format('j M'),
                    'team' => $c->teamMembers->map(fn ($u) => [
                        'id' => $u->id, 'name' => $u->name,
                    ]),
                ]),
            'filters' => ['status' => $request->string('status')->toString()],
            'statuses' => collect(ClientStatus::cases())
                ->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()]),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Client::class);

        return Inertia::render('clients/create', [
            'assignableUsers' => $this->assignableUsers(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Client::class);

        $data = $this->validated($request);
        $userIds = $request->input('user_ids', []);

        $client = Client::create($data);
        $client->users()->sync($userIds);

        Inertia::flash('message', 'Cliente creato.');

        return to_route('clients.index');
    }

    public function edit(Client $client): Response
    {
        $this->authorize('update', $client);

        return Inertia::render('clients/edit', [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'brandName' => $client->brand_name,
                'status' => $client->status->value,
                'contacts' => $client->contacts ?? [],
                'logoUrl' => $client->logo_path ? Storage::url($client->logo_path) : null,
                'toneOfVoice' => $client->tone_of_voice,
                'internalNotes' => $client->internal_notes,
                'shootingNotes' => $client->shooting_notes,
                'brandColors' => $client->brand_colors ?? [],
                'topicsArtifactUrl' => $client->topics_artifact_url,
                'shootingArtifactUrl' => $client->shooting_artifact_url,
                'userIds' => $client->users()->pluck('users.id'),
            ],
            'assignableUsers' => $this->assignableUsers(),
            'publicLink' => $this->publicLinkPayload($client),
            'accessUsers' => $this->accessUsersPayload($client),
            'artifactPrompts' => [
                'topics' => ArtifactPromptBuilder::forTopics($client, $client->currentOrLatestQuarter()),
                'shooting' => ArtifactPromptBuilder::forShooting($client),
            ],
        ]);
    }

    /** Salvataggio leggero dei soli link artifact, senza reinviare tutto il form cliente. */
    public function updateArtifactLinks(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $data = $request->validate([
            'topics_artifact_url' => ['nullable', 'url:https', 'max:2048'],
            'shooting_artifact_url' => ['nullable', 'url:https', 'max:2048'],
        ]);

        $client->update($data);

        Inertia::flash('message', 'Link artifact salvati.');

        return back();
    }

    /** @return array<int, array<string, mixed>> */
    private function accessUsersPayload(Client $client): array
    {
        return $client->pedAccessUsers()
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'email' => $user->email,
                'hasSetPassword' => $user->hasSetPassword(),
                'invitedAt' => $user->created_at->format('d/m/Y H:i'),
            ])
            ->values()
            ->all();
    }

    /** @return array<string, mixed>|null */
    private function publicLinkPayload(Client $client): ?array
    {
        $link = $client->publicLinks()->whereNull('revoked_at')->latest()->first();

        if (! $link) {
            return null;
        }

        return [
            'id' => $link->id,
            'url' => url("/ped/{$client->slug}"),
            'createdAt' => $link->created_at->format('d/m/Y H:i'),
        ];
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $data = $this->validated($request);

        // paused_at si valorizza solo alla transizione verso "in pausa".
        if ($data['status'] === 'paused' && $client->status->value !== 'paused') {
            $data['paused_at'] = now();
        } elseif ($data['status'] !== 'paused') {
            $data['paused_at'] = null;
        }

        $client->update($data);

        // Il form gestisce solo l'assegnazione del team interno: non deve mai
        // toccare gli utenti-cliente con accesso al PED (gestiti a parte da
        // ClientAccessController), altrimenti li scollega a ogni salvataggio.
        $pedAccessIds = $client->pedAccessUsers()->pluck('users.id');
        $client->users()->sync($pedAccessIds->merge($request->input('user_ids', []))->unique());

        Inertia::flash('message', 'Cliente aggiornato.');

        return to_route('clients.index');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $client->delete();   // soft delete

        Inertia::flash('message', 'Cliente archiviato.');

        return to_route('clients.index');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'brand_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,paused,archived'],
            'contacts' => ['nullable', 'array'],
            'contacts.*.name' => ['required', 'string', 'max:255'],
            'contacts.*.email' => ['nullable', 'email', 'max:255'],
            'contacts.*.phone' => ['nullable', 'string', 'max:50'],
            'contacts.*.role' => ['nullable', 'string', 'max:255'],
            'tone_of_voice' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
            'shooting_notes' => ['nullable', 'string'],
            'brand_colors' => ['nullable', 'array', 'max:3'],
            'brand_colors.*' => ['regex:/^#[0-9a-fA-F]{6}$/'],
            'topics_artifact_url' => ['nullable', 'url:https', 'max:2048'],
            'shooting_artifact_url' => ['nullable', 'url:https', 'max:2048'],
            'logo' => ['nullable', 'image', 'max:'.config('ped.logo_max_kb')],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('client-logos', 'public');
        }

        unset($validated['logo'], $validated['user_ids']);

        return $validated;
    }

    /** @return Collection<int, User> */
    private function assignableUsers(): Collection
    {
        return User::whereIn('role', ['admin', 'account_manager', 'copywriter'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'role']);
    }
}
