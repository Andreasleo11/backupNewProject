<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates a Spatie permission "nav.{route}" for every menu item in
 * NavigationService, then assigns each permission to the roles listed
 * in that item's roles[] array.
 *
 * Run:  php artisan db:seed --class=NavPermissionSeeder
 * Safe to run multiple times (idempotent via firstOrCreate).
 */
class NavPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        /**
         * Full map of  route_name => [roles_that_can_see_it]
         * Extracted from NavigationService::getFullMenu()
         */
        $navRoles = [
            // ── Administration (super-admin only) ──────────────────────────
            'admin.access-overview.index'  => ['super-admin'],
            'admin.users.index'            => ['super-admin'],
            'admin.roles.index'            => ['super-admin'],
            'admin.approval-rules.index'   => ['super-admin'],
            'admin.departments.index'      => ['super-admin'],
            'admin.employees.index'        => ['super-admin'],

            // ── Purchase Requests ──────────────────────────────────────────
            'purchase-requests.index'      => ['admin', 'super-admin', 'pr-maker', 'pr-dept-head', 'pr-head-design',
                                               'pr-gm', 'pr-purchaser', 'pr-verificator', 'pr-director', 'pr-admin',
                                               'director', 'general-manager-jakarta', 'general-manager-karawang',
                                               'head-management', 'management-staff', 'head-purchasing', 'staff-purchasing',
                                               'head-accounting', 'staff-accounting', 'head-production', 'staff-production',
                                               'head-qa', 'staff-qa', 'head-qc', 'staff-qc', 'head-warehouse', 'staff-warehouse',
                                               'pr-approver-level-1', 'pr-approver-level-2', 'pr-approver-level-3'],
            'purchase-requests.create'     => ['admin', 'super-admin', 'pr-maker', 'management-staff', 'staff-purchasing',
                                               'staff-production', 'pr-purchaser'],

            // ── HR / Evaluation ────────────────────────────────────────────
            'format.evaluation.year.allin'    => ['admin', 'super-admin', 'hr', 'manager'],
            'format.evaluation.year.yayasan'  => ['admin', 'super-admin', 'hr'],
            'format.evaluation.year.magang'   => ['admin', 'super-admin', 'hr'],
            'exportyayasan.dateinput'         => ['admin', 'super-admin', 'hr', 'finance'],

            // ── Compliance & Documentation ─────────────────────────────────
            'requirements.index'           => ['admin', 'super-admin', 'compliance', 'hr'],
            'requirements.assign'          => ['admin', 'super-admin', 'compliance', 'hr'],
            'admin.requirement-uploads'    => ['admin', 'super-admin', 'compliance'],
            'departments.overview'         => ['admin', 'super-admin', 'manager', 'hr'],
            'compliance.dashboard'         => ['admin', 'super-admin', 'compliance', 'manager'],
            'files.index'                  => ['admin', 'super-admin', 'compliance', 'hr', 'manager'],
        ];

        // Collect every permission that needs to exist
        $allPermNames = array_map(fn($route) => "nav.{$route}", array_keys($navRoles));

        // 1. Create permissions (idempotent)
        foreach ($allPermNames as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => $guard]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 2. Assign permissions to roles (addPermissionToRole is additive — won't remove others)
        foreach ($navRoles as $route => $roles) {
            $perm = Permission::where('name', "nav.{$route}")->where('guard_name', $guard)->first();
            if (! $perm) continue;

            foreach ($roles as $roleName) {
                $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
                if (! $role) continue;
                if (! $role->hasPermissionTo($perm)) {
                    $role->givePermissionTo($perm);
                }
            }
        }

        // 3. super-admin gets ALL nav.* permissions explicitly
        $superAdmin = Role::where('name', 'super-admin')->where('guard_name', $guard)->first();
        if ($superAdmin) {
            $allNavPerms = Permission::where('name', 'like', 'nav.%')->where('guard_name', $guard)->get();
            $superAdmin->syncPermissions(
                $superAdmin->permissions->merge($allNavPerms)->unique('id')
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('NavPermissionSeeder: created ' . count($allPermNames) . ' nav.* permissions and assigned to roles.');
    }
}
