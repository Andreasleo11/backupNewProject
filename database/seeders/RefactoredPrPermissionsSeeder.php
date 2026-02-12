<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Facades\Hash;

class RefactoredPrPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        // 1. Define New Permissions (Granular)
        $newPermissions = [
            // Feature: Purchase Request
            'pr.view',          // View own/dept
            'pr.view-all',      // View global
            'pr.create',
            'pr.edit',
            'pr.delete',
            'pr.cancel',
            'pr.upload-files',
            'pr.print',

            // Feature: Approval
            'approval.approve',
            'approval.reject',
            'approval.approve-items', // Item level
        ];

        foreach ($newPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => $guard]);
        }

        // 2. Define Roles & Assign Permissions
        $rolesSetup = [
            'requester' => [
                'pr.view', 'pr.create', 'pr.edit', 'pr.delete', 'pr.cancel', 'pr.upload-files', 'pr.print'
            ],
            'pr-dept-head' => [
                'pr.view', 'pr.view-all', 'pr.create', 'pr.edit', 'pr.cancel', 'pr.upload-files', 'pr.print',
                'approval.approve', 'approval.reject', 'approval.approve-items'
            ],
            'pr-verificator' => [
                'pr.view', 'pr.view-all', 'pr.print',
                'approval.approve', 'approval.reject', 'approval.approve-items'
            ],
            'pr-gm' => [
                'pr.view', 'pr.view-all', 'pr.print',
                'approval.approve', 'approval.reject', 'approval.approve-items'
            ],
            'pr-director' => [
                'pr.view', 'pr.view-all', 'pr.print',
                'approval.approve', 'approval.reject', 'approval.approve-items'
            ],
            'pr-purchaser' => [
                'pr.view', 'pr.view-all', 'pr.create', 'pr.edit', 'pr.cancel', 'pr.upload-files', 'pr.print',
                // Purchasers usually process, maybe not approve in the same sense, but let's give basic approve for now if they are part of flow
                'approval.approve' 
            ],
        ];

        foreach ($rolesSetup as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
            $role->syncPermissions($perms);
        }

        // 3. Create Test Users
        $this->createTestUser('requester', 'Requester User', 'requester@example.com', 'requester');
        $this->createTestUser('pr-dept-head', 'Dept Head User', 'head@example.com', 'pr-dept-head', 'MOULDING'); // Example Dept
        $this->createTestUser('pr-verificator', 'Verificator User', 'verificator@example.com', 'pr-verificator');
        $this->createTestUser('pr-gm', 'GM User', 'gm@example.com', 'pr-gm');
        $this->createTestUser('pr-director', 'Director User', 'director@example.com', 'pr-director');
        
        // Specific Purchasers for Filtering Test
        $this->createTestUser('pr-purchaser', 'Purchaser IT', 'purchaser_it@example.com', 'pr-purchaser', 'COMPUTER');
        $this->createTestUser('pr-purchaser', 'Purchaser Maintenance', 'purchaser_main@example.com', 'pr-purchaser', 'MAINTENANCE');
        $this->createTestUser('pr-purchaser', 'Purchaser Purchasing', 'purchaser_purc@example.com', 'pr-purchaser', 'PURCHASING');
        $this->createTestUser('pr-purchaser', 'Purchaser Personalia', 'purchaser_hrd@example.com', 'pr-purchaser', 'PERSONALIA');

        // Refresh cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function createTestUser($roleName, $name, $email, $roleSlug, $deptName = null): void
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
                // 'username' => explode('@', $email)[0], // Column does not exist
            ]
        );

        // Assign role
        $user->assignRole($roleSlug);
        
        // Assign Department if provided
        if ($deptName) {
            $dept = \App\Models\Department::where('name', $deptName)->first();
            if ($dept) {
                $user->department_id = $dept->id;
                $user->save();
            }
        }
    }
}
