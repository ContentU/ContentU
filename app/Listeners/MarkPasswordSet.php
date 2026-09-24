<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;

class MarkPasswordSet
{
    public function handle(PasswordReset $event): void
    {
        if ($event->user instanceof User && $event->user->password_set_at === null) {
            $event->user->forceFill(['password_set_at' => now()])->save();
        }
    }
}
