<?php

namespace App\Actions\Tenant\Users;

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
        Log::info('Updating tenant user', [
            'tenant_id' => tenant('id'),
            'user_id' => $user->id,
            'changes' => array_keys($data),
        ]);

        // Hash the password if provided
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        // Update the user in the tenant context
        $user->update($data);

        Log::info('Tenant user updated successfully', [
            'tenant_id' => tenant('id'),
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
            'email.unique' => 'This email already exists in this tenant',
            'password.min' => 'The password must contain at least 8 characters',
        ];
    }
}
