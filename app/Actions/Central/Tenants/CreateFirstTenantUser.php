<?php

namespace App\Actions\Central\Tenants;

use App\Enums\TenantRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateFirstTenantUser
{
    use AsAction;

    public function handle(array $data): User
    {
        $tenant = $data['tenant'];

        tenancy()->initialize($tenant);

        try {
            $existingCount = User::count();

            if ($existingCount > 0) {
                throw new \Exception("The tenant {$tenant->id} already has {$existingCount} user(s)");
            }

            $user = User::create([
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => TenantRole::TENANT_ADMIN->value,
            ]);

            Log::info('First user created in tenant', [
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

            return $user;
        } finally {
            tenancy()->end();
        }
    }

    public function rules(): array
    {
        return [
            'tenant' => ['required', 'exists:tenants,id'],
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
