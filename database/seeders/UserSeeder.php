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
        $password = env('SEED_ADMIN_PASSWORD');

        if (blank($password)) {
            if (! app()->environment('local', 'testing')) {
                throw new \RuntimeException('SEED_ADMIN_PASSWORD non impostata: seeding interrotto.');
            }
            $password = 'password';
        }

        $users = [
            ['Admin ContentU',   env('SEED_ADMIN_EMAIL', 'admin@example.com'), UserRole::Admin],
            ['Account Manager',  'am@example.com',                             UserRole::AccountManager],
            ['Copywriter',       'copy@example.com',                           UserRole::Copywriter],
        ];

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
