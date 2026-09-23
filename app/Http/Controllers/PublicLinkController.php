<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientPublicLink;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class PublicLinkController extends Controller
{
    use AuthorizesRequests;

    public function store(Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        // Revoca eventuali link attivi precedenti: un link vecchio non deve
        // restare valido a insaputa del team.
        $client->publicLinks()->whereNull('revoked_at')->update(['revoked_at' => now()]);

        ClientPublicLink::generateFor($client);

        Inertia::flash('message', 'Link pubblico generato.');

        return back();
    }

    public function destroy(ClientPublicLink $link): RedirectResponse
    {
        $this->authorize('update', $link->client);

        $link->update(['revoked_at' => now()]);

        Inertia::flash('message', 'Link pubblico revocato.');

        return back();
    }
}
