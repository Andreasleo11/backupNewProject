<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'SUPERADMIN']);
        $staffRole = Role::firstOrCreate(['name' => 'STAFF']);
        $userRole = Role::firstOrCreate(['name' => 'USER']);

        $adminPermissions = [
            'get-users',
            'store-users',
            'update-users',
            'delete-users',
            'reset-password-users',
            'delete-selected-users',
            'get-departments',
            'store-departments',
            'update-departments',
            'delete-departments',
            'get-specifications',
            'store-specifications',
            'update-specifications',
            'delete-specifications',
            'get-permissions',
            'store-permissions',
            'update-permissions',
            'delete-permissions',
            'get-users-permissions',
            'update-users-permissions',
            'get-forecast-customer-index',
            'get-discipline-index',
            'get-evaluation-index',
            'get-employee-master-index',
            'get-project-tracker-index',
            'get-holiday-list-index',
            'create-holiday-list',
            'store-holiday-list',
            'download-holiday-list-template',
            'upload-holiday-list-template',
            'delete-holiday-list',
            'update-holiday-list',
            'get-project-tracker-index',
            'get-inventory-line-list',
            'get-inventory-mtr',
            'get-inventory-fg',
            'detail-form-keluar',
            'store-form-keluar',
            'create-form-keluar',
            'get-form-keluar',
            'get-form-cuti',
            'create-form-cuti',
            'store-form-cuti',
            'detail-form-cuti',
            'get-purchase-requests',
            'create-purchase-request',
            'store-purchase-request',
            'detail-purchase-request',
            'reject-purchase-request',
            'update-purchase-request',
            'delete-purchase-request',
            'get-mould-down-index',
            'get-pps-index',
            'get-delivery-schedule-index',
            'get-pe-form-list',
            'reject-selected-director',
            'approve-selected-director',
            'get-pr-director',
            'reject-selected-vqc-report-director',
            'approve-selected-vqc-report-director',
            'reject-vqc-report-director',
            'approve-vqc-report-director',
            'detail-vqc-report-director',
            'get-vqc-reports-director',
            'delete-important-doc',
            'update-important-doc',
            'edit-important-doc',
            'detail-important-doc',
            'store-important-doc',
            'create-important-doc',
            'get-important-docs',
            'export-to-excel-vqc-report',
            'lock-vqc-report',
            'download-vqc-report',
            'delete-defect-category',
            'update-defect-category',
            'store-defect-category',
            'get-defect-categories',
            'delete-vqc-report',
            'create-vqc-report',
            'update-vqc-report',
            'edit-vqc-report',
            'detail-vqc-reports',
            'get-vqc-reports',
        ];

        $commonPermissions = [
            'get-forecast-customer-index',
            'get-discipline-index',
            'get-evaluation-index',
            'get-employee-master-index',
            'get-project-tracker-index',
            'get-holiday-list-index',
            'create-holiday-list',
            'store-holiday-list',
            'download-holiday-list-template',
            'upload-holiday-list-template',
            'delete-holiday-list',
            'update-holiday-list',
            'get-project-tracker-index',
            'get-inventory-line-list',
            'get-inventory-mtr',
            'get-inventory-fg',
            'detail-form-keluar',
            'store-form-keluar',
            'create-form-keluar',
            'get-form-keluar',
            'get-form-cuti',
            'create-form-cuti',
            'store-form-cuti',
            'detail-form-cuti',
            'get-purchase-requests',
            'create-purchase-request',
            'store-purchase-request',
            'detail-purchase-request',
            'reject-purchase-request',
            'update-purchase-request',
            'delete-purchase-request',
            'get-mould-down-index',
            'get-pps-index',
            'get-delivery-schedule-index',
            'get-pe-form-list',
            'reject-selected-director',
            'approve-selected-director',
            'get-pr-director',
            'reject-selected-vqc-report-director',
            'approve-selected-vqc-report-director',
            'reject-vqc-report-director',
            'approve-vqc-report-director',
            'detail-vqc-report-director',
            'get-vqc-reports-director',
            'delete-important-doc',
            'update-important-doc',
            'edit-important-doc',
            'detail-important-doc',
            'store-important-doc',
            'create-important-doc',
            'get-important-docs',
            'export-to-excel-vqc-report',
            'lock-vqc-report',
            'download-vqc-report',
            'delete-defect-category',
            'update-defect-category',
            'store-defect-category',
            'get-defect-categories',
            'delete-vqc-report',
            'create-vqc-report',
            'update-vqc-report',
            'edit-vqc-report',
            'detail-vqc-reports',
            'get-vqc-reports',
            'cancel-purchase-request'
        ];

        $this->createAndAssignPermissions($adminPermissions, $adminRole);
        $this->createAndAssignPermissions($commonPermissions, $staffRole);

        // Assign roles to existing users
        $this->assignRolesToExistingUsers($adminRole, $staffRole, $userRole);

        // Sync permissions for all users
        $this->syncUserPermissions();

        Permission::firstOrCreate(['name' => 'cancel-purchase-request']);
    }

    private function createAndAssignPermissions($permissions, $role)
    {
        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName]);

            // Attach permissions to the specified role
            $role->permissions()->syncWithoutDetaching($permission);
        }
    }

    private function assignRolesToExistingUsers($adminRole, $staffRole, $userRole)
    {
        // Assign the superadmin role
        User::where('role_id', 1)->each(function ($user) use ($adminRole) {
            $user->roles()->syncWithoutDetaching($adminRole->id);
        });

        // Assign the staff role
        User::where('role_id', 2)->each(function ($user) use ($staffRole) {
            $user->roles()->syncWithoutDetaching($staffRole->id);
        });

        // Assign the user role
        User::where('role_id', 3)->each(function ($user) use ($userRole) {
            $user->roles()->syncWithoutDetaching($userRole->id);
        });
    }

    private function syncUserPermissions()
    {
        // Retrieve all users
        $users = User::all();

        // Iterate over each user
        foreach ($users as $user) {
            // Get all roles associated with the user
            $roles = $user->roles;

            // Initialize an array to store permission IDs
            $permissionIdsToSync = [];

            // Retrieve permission IDs for each role
            foreach ($roles as $role) {
                $permissionIds = $role->permissions()->pluck('permissions.id')->toArray();
                $permissionIdsToSync = array_merge($permissionIdsToSync, $permissionIds);
            }

            // Retrieve user's existing permission IDs
            $existingPermissionIds = $user->permissions()->pluck('permissions.id')->toArray();

            // Merge new permission IDs with existing ones and make them unique
            $permissionIdsToSync = array_unique(array_merge($permissionIdsToSync, $existingPermissionIds));

            // Sync permission IDs to the permission_user pivot table
            $user->permissions()->sync($permissionIdsToSync);
        }
    }
}
