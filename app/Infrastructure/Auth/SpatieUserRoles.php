<?php

namespace App\Infrastructure\Auth;

use App\Application\Auth\UserRoles;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Facades\DB;

final class SpatieUserRoles implements UserRoles
{
    public function userHasRoleId(int $userId, int $roleId): bool
    {
        // during this transition period. As long as model_id matches the user id.
        return DB::table('model_has_roles')
            ->where('model_id', $userId)
            ->where('role_id', $roleId)
            ->exists();
    }

    public function getUsersWithRole(int $roleId): \Illuminate\Support\Collection
    {
        // Get user IDs from model_has_roles
        $userIds = DB::table('model_has_roles')
            ->where('role_id', $roleId)
            ->pluck('model_id');

        if ($userIds->isEmpty()) {
            return collect();
        }

        // Return User collection
        // We use the Infrastructure User model directly as it's the implementation detail here
        return User::whereIn('id', $userIds)->get();
    }
}
