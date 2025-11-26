<?php

namespace App\Actions\Central\Tenants;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateTenant
{
    use AsAction;

    public function handle(Tenant $tenant, array $data): Tenant
    {
        Log::info('Updating tenant', [
            'tenant_id' => $tenant->id,
            'changes' => $data,
        ]);

        $tenant->update($data);

        Log::info('Tenant updated successfully', [
            'tenant_id' => $tenant->id,
        ]);

        return $tenant->fresh();
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'data' => ['sometimes', 'array'],
        ];
    }
}
