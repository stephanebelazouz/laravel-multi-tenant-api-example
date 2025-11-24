<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\Tenant\Users\CreateUser;
use App\Actions\Tenant\Users\UpdateUser;
use App\Actions\Tenant\Users\DeleteUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::select('id', 'firstname', 'lastname', 'email', 'role', 'email_verified_at', 'created_at')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'users' => $users,
                'total' => $users->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving users',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = CreateUser::run($request->all());

            return response()->json([
                'message' => 'User created successfully in tenant',
                'tenant_id' => tenant('id'),
                'user' => $user,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(User $user)
    {
        try {
            return response()->json([
                'user' => $user->only([
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'role',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ]),
                'permissions' => $user->getCentralRole()?->permissions() ?? [],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, User $user)
    {
        try {
            $user = UpdateUser::run($user, $request->all());

            return response()->json([
                'message' => 'User updated successfully in tenant',
                'tenant_id' => tenant('id'),
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(User $user, Request $request)
    {
        try {

            // Prevent deleting yourself
            if ($user->id === $request->user()->id) {
                return response()->json([
                    'message' => 'You cannot delete your own account',
                ], 403);
            }

            // Prevent deleting the last super admin
            if ($user->isTenantAdmin()) {
                $tenantAdminCount = User::where('role', 'tenant_admin')->count();

                if ($tenantAdminCount <= 1) {
                    return response()->json([
                        'message' => 'Cannot delete the last tenant admin',
                    ], 403);
                }
            }

            DeleteUser::run($user);

            return response()->json([
                'message' => 'User deleted successfully in tenant',
                'tenant_id' => tenant('id'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting user in tenant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
