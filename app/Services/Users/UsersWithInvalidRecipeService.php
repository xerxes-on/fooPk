<?php

namespace App\Services\Users;

use Illuminate\Support\Facades\DB;

class UsersWithInvalidRecipeService
{
    public function checkIfUserNotified(int $userId): bool
    {
        $user = DB::table('users_with_invalid_recipes')
            ->where('user_id', $userId)
            ->first();
        return $user && dateDiffInDays($user->notified_at, now()) < config('foodpunk.users.interval_for_email_sending');
    }

    public function createUserWithInvalidRecipe(int $userId): bool
    {
        return DB::table('users_with_invalid_recipes')
            ->updateOrInsert(
                ['user_id' => $userId],
                ['user_id' => $userId, 'notified_at' => now()],
            );
    }

    public function deleteUserFromInvalidRecipe(int $userId): bool
    {
        return DB::table('users_with_invalid_recipes')
            ->where('user_id', $userId)
            ->delete();
    }
}
