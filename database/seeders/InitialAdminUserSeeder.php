<?php

namespace Database\Seeders;

use App\Enums\CentralRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InitialAdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::create([
            'firstname' => 'Super',
            'lastname' => 'Admin',
            'email' => env('INITIAL_ADMIN_USER_MAIL'),
            'password' => Hash::make(env('INITIAL_ADMIN_USER_PASSWORD')),
            'role' => CentralRole::CENTRAL_ADMIN->value,
        ]);

        $user->createToken('auth-token')->plainTextToken;
    }
}
