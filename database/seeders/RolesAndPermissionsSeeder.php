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

            // === Generic PR approval roles (used by approval engine) ===

            // Everyone who can create a PR
            'pr-maker' => [
                'pr.view-own',
                'pr.create',
                'pr.update',
                'pr.submit',
                'pr.cancel',
            ],

            // Department heads who approve PR from their department
            'pr-dept-head' => [
                'pr.view-dept',
                'pr.view-own',
                'pr.approve',
            ],

            // Special head for MOULDING design PRs
            'pr-head-design' => [
                'pr.view-dept',
                'pr.view-any',
                'pr.approve',
            ],

            // GM approvers for factory (non-office) departments
            'pr-gm' => [
                'pr.view-dept',
                'pr.view-any',
                'pr.approve',
            ],

            // Purchasers who handle PR after dept head / GM
            'pr-purchaser' => [
                'pr.view-dept',
                'pr.view-any',
                'pr.create',   // can create/modify PRs for purchasing if needed
                'pr.update',
                'pr.submit',
                'pr.cancel',
                'pr.approve',
            ],

            // Personalia / verificator step for COMPUTER & PERSONALIA PRs
            'pr-verificator' => [
                'pr.view-dept',
                'pr.view-any',
                'pr.approve',
            ],

            // Director signers for PR
            'pr-director' => [
                'pr.view-any',
                'pr.approve',
                'approval.view-log',
            ],

            // Admins for approval engine (could be same as it-admin, but separated semantically)
            'pr-admin' => [
                'pr.view-any',
                'approval.manage-rules',
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

            // === Old workflow-specific PR approvers (can keep for now) ===
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
