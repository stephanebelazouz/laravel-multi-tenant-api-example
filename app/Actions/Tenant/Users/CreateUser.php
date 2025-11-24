<?php

namespace App\Actions\Tenant\Users;

use App\Enums\TenantRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateUser
{
    use AsAction;

    public function handle(array $data): User
    {
        $tenantId = tenant('id');

        Log::info('Creating user in tenant', [
            'tenant_id' => $tenantId,
            'email' => $data['email'],
        ]);

        $user = User::create([
            'firstname' => $data['firstname'] ?? '',
            'lastname' => $data['lastname'] ?? '',
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? TenantRole::TENANT_USER->value,
        ]);

        Log::info('User created successfully in tenant', [
            'tenant_id' => $tenantId,
            'user_id' => $user->id,
        ]);

        return $user;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'email.unique' => 'The email already exists in this tenant',
        ];
    }
}
