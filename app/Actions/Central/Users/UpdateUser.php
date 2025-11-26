<?php

namespace App\Actions\Central\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateUser
{
    use AsAction;

    public function handle(User $user, array $data): User
    {
        Log::info('Updating central user', [
            'user_id' => $user->id,
            'changes' => array_keys($data),
        ]);

        // Hash the password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Update the user
        $user->update($data);

        Log::info('Central user updated successfully', [
            'user_id' => $user->id,
        ]);

        return $user->fresh();
    }

    public function rules(): array
    {
        $userId = $this->user->id ?? null;

        return [
            'firstname' => ['sometimes', 'string', 'max:255'],
            'lastname' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => ['sometimes', 'string', 'min:8'],
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
