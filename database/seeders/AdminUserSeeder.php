<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Создаёт администратора по умолчанию.
     * Пароль задайте в .env: GND_ADMIN_EMAIL, GND_ADMIN_PASSWORD (если не заданы — admin@localhost / password).
     */
    public function run(): void
    {
        $email = env('GND_ADMIN_EMAIL', 'admin@localhost');
        $password = env('GND_ADMIN_PASSWORD', 'password');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrator',
                'password' => Hash::make($password),
                'role' => User::ROLE_ADMIN,
            ]
        );
    }
}
