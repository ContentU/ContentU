<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\User;
use App\Notifications\ClientPedInvite;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ClientAccessController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if ($user && $user->role !== UserRole::Client) {
            return back()->withErrors(['email' => 'Questa email appartiene già a un utente interno del team.']);
        }

        if ($user && $client->pedAccessUsers()->whereKey($user->id)->exists()) {
            return back()->withErrors(['email' => 'Questa email ha già accesso al PED di questo cliente.']);
        }

        if (! $user) {
            $user = User::create([
                'name' => Str::before($data['email'], '@'),
                'email' => $data['email'],
                // Placeholder inutilizzabile: l'accesso vero parte dal link di reset password nell'invito.
                'password' => Hash::make(Str::random(40)),
                'role' => UserRole::Client->value,
                'is_active' => true,
            ]);
        }

        $client->users()->attach($user->id);

        $this->sendInvite($user, $client);

        Inertia::flash('message', 'Invito inviato.');

        return back();
    }

    public function destroy(Client $client, User $user): RedirectResponse
    {
        $this->authorize('update', $client);

        abort_unless($user->role === UserRole::Client, 404);

        $client->users()->detach($user->id);

        Inertia::flash('message', 'Accesso rimosso.');

        return back();
    }

    public function resend(Client $client, User $user): RedirectResponse
    {
        $this->authorize('update', $client);

        abort_unless($client->pedAccessUsers()->whereKey($user->id)->exists(), 404);

        $this->sendInvite($user, $client);

        Inertia::flash('message', 'Invito inviato di nuovo.');

        return back();
    }

    private function sendInvite(User $user, Client $client): void
    {
        $token = Password::broker()->createToken($user);

        Notification::send($user, new ClientPedInvite($token, $client));
    }
}
