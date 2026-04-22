<?php

namespace App\Domain\Admin\Services;

use App\Infrastructure\Common\PermissionRegistry;
use App\Models\PermissionSyncLog;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionAuditService
{
    /**
     * Get a complete mapping of all roles and their permissions in the database.
     * Use a consistent format: [role_name => [permission_name, ...]]
     */
    public function getCurrentState(): array
    {
        return Role::with('permissions')
            ->get()
            ->mapWithKeys(function ($role) {
                return [$role->name => $role->permissions->pluck('name')->toArray()];
            })
            ->toArray();
    }

    /**
     * Compare two states and calculate the differences.
     * Returns an array of [role_name => ['added' => [...], 'removed' => [...]]]
     */
    public function calculateDiff(array $before, array $after): array
    {
        $diff = [];
        $allRoles = array_unique(array_merge(array_keys($before), array_keys($after)));

        foreach ($allRoles as $role) {
            $beforePerms = $before[$role] ?? [];
            $afterPerms = $after[$role] ?? [];

            $added = array_diff($afterPerms, $beforePerms);
            $removed = array_diff($beforePerms, $afterPerms);

            if (! empty($added) || ! empty($removed)) {
                $diff[$role] = [
                    'added' => array_values($added),
                    'removed' => array_values($removed),
                ];
            }
        }

        return $diff;
    }

    /**
     * Compare a state versus the PermissionRegistry definition.
     */
    /**
     * Get the synchronization state, separating roles into managed and unmanaged.
     */
    public function getSyncState(): array
    {
        $current = $this->getCurrentState();
        $registry = PermissionRegistry::allRolesWithPermissions();

        // Registry might use '*' which means all permissions
        $allPermissions = Permission::all()->pluck('name')->toArray();
        foreach ($registry as $role => $perms) {
            if (in_array('*', $perms, true)) {
                $registry[$role] = $allPermissions;
            }
        }

        // Managed: Roles that exist in the registry
        $managedCurrent = array_intersect_key($current, $registry);
        $managed = $this->calculateDiff($managedCurrent, $registry);

        // Unmanaged: Roles that exist in DB but NOT in the registry
        $unmanagedRoles = array_diff_key($current, $registry);

        return [
            'managed' => $managed,
            'unmanaged' => $unmanagedRoles,
        ];
    }

    /**
     * Revert roles and permissions to a specific snapshot.
     */
    public function revert(PermissionSyncLog $log): void
    {
        $snapshot = $log->snapshot;

        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($snapshot as $roleName => $perms) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($perms);
        }

        // Cleanup roles that are in DB but NOT in snapshot?
        // To be truly a "reversion", we should probably remove roles that weren't in the snapshot.
        $allCurrentRoles = Role::pluck('name')->toArray();
        $rolesToDelete = array_diff($allCurrentRoles, array_keys($snapshot));

        foreach ($rolesToDelete as $roleName) {
            Role::findByName($roleName)->delete();
        }
    }
}
