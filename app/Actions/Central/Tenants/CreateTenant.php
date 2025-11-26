<?php

namespace App\Actions\Central\Tenants;

use App\Actions\Central\Tenants\CreateFirstTenantUser;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateTenant
{
    use AsAction;

    public function handle(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            Log::info('Creating tenant', [
                'name' => $data['name'],
            ]);

            $tenant = Tenant::create([
                'name' => $data['name'],
                'data' => $data['data'] ?? [],
            ]);

            Log::info('Tenant created with UUID', [
                'tenant_id' => $tenant->id,
                'name' => $tenant->name,
            ]);

            if ($data['admin_email'] && $data['admin_password']) {
                CreateFirstTenantUser::run([
                    'tenant' => $tenant,
                    'firstname' => $data['admin_firstname'] ?? '',
                    'lastname' => $data['admin_lastname'] ?? '',
                    'email' => $data['admin_email'],
                    'password' => $data['admin_password'],
                ]);
            }

            return $tenant;
        });
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'data' => ['sometimes', 'array'],
            'admin_email' => ['sometimes', 'required_with:admin_password', 'email'],
            'admin_password' => ['sometimes', 'required_with:admin_email', 'string', 'min:8'],
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'name.required' => 'name is required',
            'admin_email.email' => 'admin_email must be a valid email',
            'admin_password.min' => 'admin_password must be at least 8 characters',
        ];
    }
}
