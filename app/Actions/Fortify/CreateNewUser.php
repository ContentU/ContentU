<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        // Il cliente viene messo in sessione dal middleware del link pubblico (Fase 07).
        // Senza di esso non si può registrare nessuno: nessun utente interno nasce da /register.
        $clientId = session('ped.public_link.client_id');

        abort_if($clientId === null, 403, 'La registrazione è possibile solo da un link PED valido.');

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'role' => UserRole::Client->value,
            'is_active' => true,
        ]);

        $user->clients()->attach($clientId);

        return $user;
    }
}
