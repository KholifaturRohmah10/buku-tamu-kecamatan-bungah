<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->createRoleUser(
            name: env('ADMIN_NAME', 'Administrator'),
            email: env('ADMIN_EMAIL', 'admin@bukutamu.test'),
            password: env('ADMIN_PASSWORD', 'admin12345'),
            role: User::ROLE_ADMIN,
        );

        $this->createRoleUser(
            name: env('VALIDATOR_NAME', 'Petugas Validator'),
            email: env('VALIDATOR_EMAIL', 'validator@bukutamu.test'),
            password: env('VALIDATOR_PASSWORD', 'validator12345'),
            role: User::ROLE_VALIDATOR,
        );

        $this->createRoleUser(
            name: env('GUEST_NAME', 'Akun Tamu Demo'),
            email: env('GUEST_EMAIL', 'tamu@bukutamu.test'),
            password: env('GUEST_PASSWORD', 'tamu12345'),
            role: User::ROLE_TAMU,
        );
    }

    private function createRoleUser(string $name, string $email, string $password, string $role): void
    {
        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'email_verified_at' => now(),
                'role' => $role,
                'is_admin' => $role === User::ROLE_ADMIN,
                'password' => $password,
                'remember_token' => Str::random(10),
            ]
        );
    }
}
