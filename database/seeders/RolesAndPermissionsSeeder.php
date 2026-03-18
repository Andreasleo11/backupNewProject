<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        /*
         |--------------------------------------------------------------
         | 1. Define permissions
         |--------------------------------------------------------------
         */

        $permissions = \App\Infrastructure\Common\PermissionRegistry::allPermissions();

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => $guard]
            );
        }

        /*
         |--------------------------------------------------------------
         | 2. Define roles and their permissions
         |--------------------------------------------------------------
         */

        $rolesPermissions = \App\Infrastructure\Common\PermissionRegistry::allRolesWithPermissions();

        /*
         |--------------------------------------------------------------
         | 3. Create roles & attach permissions
         |--------------------------------------------------------------
         */

        foreach ($rolesPermissions as $roleName => $perms) {
            /** @var Role $role */
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => $guard]
            );

            if (in_array('*', $perms, true)) {
                $role->syncPermissions(Permission::all());
            } else {
                $role->syncPermissions($perms);
            }
        }

        // Refresh cache again after assigning
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
