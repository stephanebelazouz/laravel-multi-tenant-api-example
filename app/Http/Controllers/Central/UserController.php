<?php

namespace App\Http\Controllers\Central;

use App\Actions\Central\Users\CreateUser;
use App\Actions\Central\Users\UpdateUser;
use App\Actions\Central\Users\DeleteUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of central users
     */
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

    /**
     * Store a newly created central user
     */
    public function store(Request $request)
    {
        try {
            $user = CreateUser::run($request->all());

            return response()->json([
                'message' => 'User created successfully',
                'user' => $user,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        try {
            return response()->json([
                'user' => $user->only([
                    'id',
                    'firstname',
                    'lastname',
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

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        try {
            $updatedUser = UpdateUser::run($user, $request->all());

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $updatedUser,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified user
     */
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
            if ($user->isCentralAdmin()) {
                $centralAdminCount = User::where('role', 'central_admin')->count();

                if ($centralAdminCount <= 1) {
                    return response()->json([
                        'message' => 'Cannot delete the last central admin',
                    ], 403);
                }
            }

            DeleteUser::run($user);

            return response()->json([
                'message' => 'User deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
