<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('settings/users', [
            'users' => User::whereIn('role', UserRole::internal())
                ->with('clients:id,name')
                ->orderBy('name')
                ->get()
                ->map(fn (User $u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'role' => $u->role->value,
                    'isActive' => $u->is_active,
                    'clients' => $u->clients->pluck('name'),
                ]),
            'roles' => collect(UserRole::internal())
                ->map(fn (UserRole $r) => ['value' => $r->value, 'label' => $r->label()]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', Rule::in(array_map(fn ($r) => $r->value, UserRole::internal()))],
        ]);

        $user = User::create([
            ...$data,
            'password' => Hash::make(Str::random(32)),
            'is_active' => true,
        ]);

        Password::sendResetLink(['email' => $user->email]);

        Inertia::flash('message', "Invito inviato a {$user->email}.");

        return back();
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in(array_map(fn ($r) => $r->value, UserRole::internal()))],
        ]);

        $user->update($data);

        Inertia::flash('message', 'Utente aggiornato.');

        return back();
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 422, 'Non puoi disattivare te stesso.');

        $user->update(['is_active' => ! $user->is_active]);

        return back();
    }
}
