<?php

namespace App\Actions\Central\Users;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteUser
{
    use AsAction;

    public function handle(User $user): bool
    {
        Log::info('Deleting central user', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        // Delete all tokens
        $user->tokens()->delete();

        // Delete the user
        $user->delete();

        Log::info('Central user deleted successfully', [
            'user_id' => $user->id,
        ]);

        return true;
    }
}
