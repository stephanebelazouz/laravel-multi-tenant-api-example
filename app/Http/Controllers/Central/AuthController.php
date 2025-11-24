<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login - Central connection
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'tenant_id' => 'sometimes|exists:tenants,id',
        ]);

        // Search for the user in the central database
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The credentials are incorrect.'],
            ]);
        }

        // Verify access to the tenant if specified
        if ($request->tenant_id) {
            $tenant = Tenant::find($request->tenant_id);

            if (!$tenant) {
                throw ValidationException::withMessages([
                    'tenant_id' => ['The organisation was not found.'],
                ]);
            }

            // Verify that the user has access to this tenant
            // if (!$user->tenants->contains($tenant->id)) {
            //     throw ValidationException::withMessages([
            //         'tenant_id' => ['You do not have access to this organisation.'],
            //     ]);
            // }
        }

        // Generate the token
        $token = $user->createToken('auth-token', ['*'])->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Me - Current user information
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * Refresh - Refresh token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();

        // Supprime l'ancien token
        $request->user()->currentAccessToken()->delete();

        // Crée un nouveau token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * List accessible tenants
     */
    public function tenants(Request $request)
    {
        $user = $request->user();

        // If you have a many-to-many relationship between User and Tenant
        // $tenants = $user->tenants;

        // Otherwise, return all tenants (to be adapted according to your needs)
        $tenants = Tenant::all();

        return response()->json([
            'tenants' => $tenants,
        ]);
    }
}
