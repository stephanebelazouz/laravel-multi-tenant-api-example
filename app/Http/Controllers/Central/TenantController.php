<?php

namespace App\Http\Controllers\Central;

use App\Actions\Central\Tenants\CreateFirstTenantUser;
use App\Actions\Central\Tenants\CreateTenant;
use App\Actions\Central\Tenants\UpdateTenant;
use App\Actions\Central\Tenants\DeleteTenant;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index()
    {
        return response()->json([
            'tenants' => Tenant::all(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $tenant = CreateTenant::run($request->all());

            return response()->json([
                'message' => 'Tenant created successfully',
                'tenant' => $tenant,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating tenant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Tenant $tenant)
    {
        return response()->json([
            'tenant' => $tenant,
        ]);
    }

    public function update(Request $request, Tenant $tenant)
    {
        try {
            $tenant = UpdateTenant::run($tenant, $request->all());

            return response()->json([
                'message' => 'Tenant updated successfully',
                'tenant' => $tenant,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating tenant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Tenant $tenant)
    {
        try {
            DeleteTenant::run($tenant);

            return response()->json([
                'message' => 'Tenant deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting tenant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createFirstUser(Request $request, Tenant $tenant)
    {
        try {
            $user = CreateFirstTenantUser::run([
                'tenant' => $tenant,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            return response()->json([
                'message' => 'First user created successfully in tenant',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating first user in tenant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
