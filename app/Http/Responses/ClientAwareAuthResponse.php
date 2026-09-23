<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;

/**
 * Risposta condivisa da login e registrazione: un cliente esterno non
 * entra mai nell'area interna. Se ha appena aperto/registrato da un link
 * PED valido per il SUO cliente, atterra sul portale pubblico; altrimenti
 * viene sloggato subito (accesso all'area interna sempre negato).
 */
class ClientAwareAuthResponse implements LoginResponse, RegisterResponse
{
    public function toResponse($request)
    {
        /** @var Request $request */
        $user = $request->user();

        if ($user->isClient()) {
            $token = $request->session()->get('ped.public_link.token');
            $clientId = $request->session()->get('ped.public_link.client_id');

            if ($token !== null && $clientId !== null
                && $user->clients()->whereKey($clientId)->exists()) {
                return redirect()->route('ped.feed', $token);
            }

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => "Quest'area è riservata al team. Accedi dal link del tuo PED.",
            ]);
        }

        return redirect()->intended(route('dashboard'));
    }
}
