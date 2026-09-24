<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = config('ped.seed.admin_password');

        if (blank($password)) {
            if (! app()->environment('local', 'testing')) {
                throw new \RuntimeException('SEED_ADMIN_PASSWORD non impostata: seeding interrotto.');
            }
            $password = 'password';
        }

        if (! is_string($password)) {
            throw new \RuntimeException('SEED_ADMIN_PASSWORD deve essere una stringa.');
        }

        $users = [
            ['Admin ContentU',   config('ped.seed.admin_email'), UserRole::Admin],
        ];

        // Utenti demo con email prevedibili: solo in locale/test, mai in produzione.
        if (app()->environment('local', 'testing')) {
            $users[] = ['Account Manager',  'am@example.com',   UserRole::AccountManager];
            $users[] = ['Copywriter',       'copy@example.com', UserRole::Copywriter];
        }

        foreach ($users as [$name, $email, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make($password),
                    'role' => $role->value,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
