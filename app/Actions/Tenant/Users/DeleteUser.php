<?php

namespace App\Actions\Tenant\Users;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteUser
{
    use AsAction;

    public function handle(User $user): bool
    {
        Log::info('Deleting tenant user', [
            'tenant_id' => tenant('id'),
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        // Delete all user tokens
        $user->tokens()->delete();

        // Delete the user
        $user->delete();

        Log::info('Tenant user deleted successfully', [
            'tenant_id' => tenant('id'),
            'user_id' => $user->id,
        ]);

        return true;
    }

    /**
     * Optional validation rules
     * For example, prevent deleting the last admin
     */
    public function authorize(): bool
    {
        // Optional: prevent deleting the last user
        $userCount = User::count();

        if ($userCount <= 1) {
            throw new \Exception('Cannot delete the last user of the tenant');
        }

        return true;
    }
}
