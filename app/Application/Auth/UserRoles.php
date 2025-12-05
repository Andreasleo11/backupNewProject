<?php

namespace App\Application\Auth;

interface UserRoles
{
    /**
     * Check if a given user has the given role ID (Spatie role id)
     */
    public function userHasRoleId(int $userId, int $roleId): bool;
}