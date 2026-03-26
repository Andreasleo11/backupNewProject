<?php

namespace App\Application\Auth;

interface UserRoles
{
    /**
     * Check if a given user has the given role ID (Spatie role id)
     */
    public function userHasRoleId(int $userId, int $roleId): bool;

    /**
     * Get all users having the given role ID.
     *
     * @return \Illuminate\Support\Collection<int, \App\Infrastructure\Persistence\Eloquent\Models\User>
     */
    public function getUsersWithRole(int $roleId): \Illuminate\Support\Collection;
}
