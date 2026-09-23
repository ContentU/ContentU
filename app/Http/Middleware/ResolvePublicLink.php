<?php

namespace App\Http\Middleware;

use App\Models\Client;
use App\Models\ClientPublicLink;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolvePublicLink
{
    public function handle(Request $request, Closure $next): Response
    {
        $client = Client::where('slug', $request->route('clientSlug'))->first();

        $link = $client
            ? ClientPublicLink::where('client_id', $client->id)
                ->whereNull('revoked_at')
                ->latest()
                ->first()
            : null;

        // 404 generico: non rivelare se lo slug è inesistente, se l'accesso è
        // stato revocato, o se il cliente esiste. Nessun messaggio diverso fra i casi.
        abort_if($link === null, 404);

        $link->setRelation('client', $client);

        // Serve a CreateNewUser (Fase 01.5) per legare il nuovo utente
        // al cliente giusto. Solo scalari: la sessione è serializzata in JSON.
        $request->session()->put('ped.public_link.client_id', $link->client_id);
        $request->session()->put('ped.public_link.client_slug', $client->slug);

        $user = $request->user();

        // Il controllo decisivo: un utente autenticato che accede a questo
        // link deve essere legato a QUESTO cliente. Non basta filtrare le query.
        if ($user !== null) {
            $belongs = $user->isClient()
                ? $user->clients()->whereKey($link->client_id)->exists()
                : $user->isInternal();   // il team può aprire l'anteprima del portale

            abort_unless($belongs, 404);
        }

        $request->attributes->set('publicLink', $link);

        return $next($request);
    }
}
