<?php

namespace App\Support;

use App\Models\Client;
use App\Models\User;
use App\Notifications\ClientPedInvite;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

/**
 * Un solo posto per inviare l'invito al PED: lo usano sia l'admin dalla
 * scheda cliente (ClientAccessController) sia il flusso pubblico quando
 * un'email in "pending_invite" richiede di nuovo il link (PublicPedController).
 */
class PedInvite
{
    public static function send(User $user, Client $client): void
    {
        $token = Password::broker()->createToken($user);

        Notification::send($user, new ClientPedInvite($token, $client));
    }
}
