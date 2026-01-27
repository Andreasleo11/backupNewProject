<?php

namespace App\Infrastructure\Auth;

use App\Application\Auth\UserRoles;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Facades\DB;

final class SpatieUserRoles implements UserRoles
{
    public function userHasRoleId(int $userId, int $roleId): bool
    {
        // Query the roles table directly to be robust against class name variations (App\Models\User vs Infrastructure\...)
        // during this transition period. As long as model_id matches the user id.
        return DB::table('model_has_roles')
            ->where('model_id', $userId)
            ->where('role_id', $roleId)
            ->exists();
    }
}
