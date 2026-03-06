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
            'pr.view',
            'pr.view-all',
            'pr.create',
            'pr.edit',
            'pr.delete',
            'pr.cancel',
            'pr.upload-files',
            'pr.print',
            'pr.batch-approve',  // bulk approve/reject multiple PRs at once (director-level)

            // Approval engine
            'approval.view-log',
            'approval.manage-rules',   // RuleTemplate / RuleStepTemplate CRUD
            'approval.approve',
            'approval.reject',
            'approval.approve-items',

            // Evaluation & Discipline
            'evaluation.view-any',
            'evaluation.view-department',
            'evaluation.grade',
            'evaluation.approve-department',
            'evaluation.approve-final',
            // Tab-scoped access (fine-grained override per user/role)
            'evaluation.view-regular',
            'evaluation.view-yayasan',
            'evaluation.view-magang',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(
                ['name' => $permName, 'guard_name' => $guard]
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

            // === Dynamic PR approval roles (used by approval engine) ===
            'requester' => [
                'pr.view', 'pr.create', 'pr.edit', 'pr.delete', 'pr.cancel', 'pr.upload-files', 'pr.print',
            ],

            'pr-dept-head' => [
                'pr.view', 'pr.view-all', 'pr.create', 'pr.edit', 'pr.cancel', 'pr.upload-files', 'pr.print',
                'approval.approve', 'approval.reject', 'approval.approve-items',
            ],

            'pr-verificator' => [
                'pr.view', 'pr.view-all', 'pr.print',
                'approval.approve', 'approval.reject', 'approval.approve-items',
            ],

            'pr-gm' => [
                'pr.view', 'pr.view-all', 'pr.print',
                'approval.approve', 'approval.reject', 'approval.approve-items',
            ],

            'pr-director' => [
                'pr.view', 'pr.view-all', 'pr.print',
                'approval.approve', 'approval.reject', 'approval.approve-items', 'pr.batch-approve',
            ],

            'pr-purchaser' => [
                'pr.view', 'pr.view-all', 'pr.create', 'pr.edit', 'pr.cancel', 'pr.upload-files', 'pr.print',
                'approval.approve',
            ],
            
            'pr-admin' => [
                'pr.view-all', 'pr.batch-approve', 'approval.manage-rules', 'approval.view-log',
            ],

            // === Company management (Legacy / General Roles) ===
            'director' => [
                'pr.view-all', 'approval.approve', 'approval.reject', 'approval.approve-items', 'pr.batch-approve', 'approval.view-log',
            ],

            // === Evaluation & HR ===
            'department-head' => [
                'evaluation.view-department',
                'evaluation.grade',
                'evaluation.approve-department',
                // Dept heads see all 3 tabs for their own department
                'evaluation.view-regular',
                'evaluation.view-yayasan',
                'evaluation.view-magang',
            ],

            'hrd-manager' => [
                'evaluation.view-any',
                'evaluation.approve-final',
                'evaluation.view-regular',
                'evaluation.view-yayasan',
                'evaluation.view-magang',
            ],

            'general-manager' => [
                'evaluation.view-any',
                'evaluation.approve-final',
                'evaluation.view-regular',
                'evaluation.view-yayasan',
                'evaluation.view-magang',
            ],
        ];

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

            if ($perms === ['*']) {
                $role->syncPermissions(Permission::all());
            } else {
                $role->syncPermissions($perms);
            }
        }

        // Refresh cache again after assigning
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
