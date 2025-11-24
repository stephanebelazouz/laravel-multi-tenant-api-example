<?php

namespace App\Actions\Central\Tenants;

use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteTenant
{
    use AsAction;

    public function handle(Tenant $tenant): bool
    {
        $tenantId = $tenant->id;

        Log::info('Deleting tenant', [
            'tenant_id' => $tenantId,
        ]);

        // The database deletion is handled by events
        $tenant->delete();

        Log::info('Tenant deleted successfully', [
            'tenant_id' => $tenantId,
        ]);

        return true;
    }
}
