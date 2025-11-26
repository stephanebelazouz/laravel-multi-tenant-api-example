<?php

namespace App\Actions\Central\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateUser
{
    use AsAction;

    public function handle(array $data): User
    {
        Log::info('Creating central user', [
            'email' => $data['email'],
        ]);

        // Create the user in the central database
        $user = User::create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Log::info('Central user created successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return $user;
    }

    public function rules(): array
    {
        return [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'email.unique' => 'This email is already used',
            'password.min' => 'The password must contain at least 8 characters',
        ];
    }
}
