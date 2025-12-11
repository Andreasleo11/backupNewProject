<?php

namespace App\Infrastructure\Auth;

use App\Application\Auth\UserRoles;
use App\Infrastructure\Persistence\Eloquent\Models\User;

final class SpatieUserRoles implements UserRoles
{
    public function userHasRoleId(int $userId, int $roleId): bool
    {
        $user = User::query()->find($userId);

        if(!$user) {
            return false;
        }

        return $user->roles()->where($roleId)->exists();
    }
}