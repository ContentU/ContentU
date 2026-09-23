<?php

namespace App\Policies;

use App\Models\Content;
use App\Models\User;

class ContentPolicy
{
    /** Vale per ogni azione: devi essere assegnato al cliente di questo contenuto. */
    private function belongsToClient(User $user, Content $content): bool
    {
        return $user->clients()
            ->whereKey($content->quarter->client_id)
            ->exists();
    }

    public function view(User $user, Content $content): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isClient()) {
            return $this->belongsToClient($user, $content);
        }

        return $user->isInternal() && $this->belongsToClient($user, $content);
    }

    public function update(User $user, Content $content): bool
    {
        // Il cliente non modifica MAI il testo: decisione del capo, corretta due volte.
        if ($user->isClient()) {
            return false;
        }

        return $user->isAdmin() || $this->belongsToClient($user, $content);
    }

    /** Approvare e pubblicare: solo admin e account manager. Il copywriter no. */
    public function approve(User $user, Content $content): bool
    {
        if (! ($user->isAdmin() || $user->isAccountManager())) {
            return false;
        }

        return $user->isAdmin() || $this->belongsToClient($user, $content);
    }

    public function publish(User $user, Content $content): bool
    {
        return $this->approve($user, $content);
    }

    public function comment(User $user, Content $content): bool
    {
        return $this->view($user, $content);
    }

    public function delete(User $user, Content $content): bool
    {
        return $user->isAdmin();
    }
}
