<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        DB::table('role_has_permissions')->truncate();

        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        /*
         |--------------------------------------------------------------
         | 1. Define permissions
         |--------------------------------------------------------------
         |
         | Keep names consistent and future-proof. You can always add
         | more as modules grow.
         |
         */

        $permissions = [
            // User management
            'user.view-any',
            'user.view',
            'user.create',
            'user.update',
            'user.delete',
            'user.change-password',

            // Roles & permissions
            'role.view-any',
            'role.create',
            'role.update',
            'role.delete',
            'permission.view-any',

            // Departments
            'department.view-any',
            'department.create',
            'department.update',
            'department.delete',

            // Employees
            'employee.view-any',
            'employee.view',
            'employee.update',

            // Purchase Request (PR)
            'pr.view-own',
            'pr.view-dept',
            'pr.view-any',
            'pr.create',
            'pr.update',
            'pr.cancel',
            'pr.submit',
            'pr.approve',

            // Approval engine
            'approval.view-log',
            'approval.manage-rules',   // RuleTemplate / RuleStepTemplate CRUD
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => $guard],
                []
            );
        }

        /*
         |--------------------------------------------------------------
         | 2. Define roles and their permissions
         |--------------------------------------------------------------
         |
         | '*' means "all permissions in the system".
         | You can tune each role's list later.
         |
         */

        $rolesPermissions = [

            // === System / technical ===
            'super-admin' => ['*'],

            'it-admin' => [
                // user & roles
                'user.view-any',
                'user.view',
                'user.create',
                'user.update',
                'user.delete',
                'user.change-password',

                'role.view-any',
                'role.create',
                'role.update',
                'role.delete',
                'permission.view-any',

                // departments & employees
                'department.view-any',
                'department.create',
                'department.update',
                'department.delete',

                'employee.view-any',
                'employee.view',
                'employee.update',

                // approval rules
                'approval.manage-rules',
                'approval.view-log',
            ],

            'auditor' => [
                'user.view-any',
                'user.view',

                'department.view-any',

                'employee.view-any',
                'employee.view',

                'pr.view-any',
                'approval.view-log',
            ],

            // === Company management ===
            'director' => [
                'pr.view-any',
                'pr.approve',
                'approval.view-log',
            ],

            'general-manager-jakarta' => [
                'pr.view-dept',
                'pr.view-any',
                'pr.approve',
                'approval.view-log',
            ],

            'general-manager-karawang' => [
                'pr.view-dept',
                'pr.view-any',
                'pr.approve',
                'approval.view-log',
            ],

            // === Departments: Management ===
            'head-management' => [
                'pr.view-dept',
                'pr.approve',
            ],
            'management-staff' => [
                'pr.view-own',
                'pr.create',
                'pr.update',
                'pr.submit',
                'pr.cancel',
            ],

            // === Departments: Purchasing ===
            'head-purchasing' => [
                'pr.view-dept',
                'pr.approve',
            ],
            'staff-purchasing' => [
                'pr.view-dept',
                'pr.create',
                'pr.update',
                'pr.submit',
                'pr.cancel',
            ],

            // === Departments: Accounting ===
            'head-accounting' => [
                'pr.view-dept',
                'pr.approve',
            ],
            'staff-accounting' => [
                'pr.view-dept',
                'pr.view-own',
            ],

            // === Departments: Production ===
            'head-production' => [
                'pr.view-dept',
                'pr.approve',
            ],
            'staff-production' => [
                'pr.view-own',
                'pr.create',
                'pr.update',
                'pr.submit',
                'pr.cancel',
            ],

            // === Departments: QA ===
            'head-qa' => [
                'pr.view-dept',
                'pr.approve',
            ],
            'staff-qa' => [
                'pr.view-dept',
                'pr.view-own',
            ],

            // === Departments: QC ===
            'head-qc' => [
                'pr.view-dept',
                'pr.approve',
            ],
            'staff-qc' => [
                'pr.view-dept',
                'pr.view-own',
            ],

            // === Departments: Warehouse ===
            'head-warehouse' => [
                'pr.view-dept',
                'pr.approve',
            ],
            'staff-warehouse' => [
                'pr.view-dept',
                'pr.view-own',
            ],

            // === Workflow-specific PR approvers ===
            'pr-approver-level-1' => [
                'pr.view-dept',
                'pr.view-any',
                'pr.approve',
            ],
            'pr-approver-level-2' => [
                'pr.view-dept',
                'pr.view-any',
                'pr.approve',
            ],
            'pr-approver-level-3' => [
                'pr.view-dept',
                'pr.view-any',
                'pr.approve',
            ],
        ];

        /*
         |--------------------------------------------------------------
         | 3. Create roles & attach permissions
         |--------------------------------------------------------------
         */

        foreach ($rolesPermissions as $roleName => $perms) {
            /** @var \Spatie\Permission\Models\Role $role */
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => $guard],
                []
            );

            if ($perms === ['*']) {
                // Give all permissions
                $role->syncPermissions(Permission::all());
            } else {
                $role->syncPermissions($perms);
            }
        }

        // Refresh cache again after assigning
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
