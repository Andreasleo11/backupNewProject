<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PrTestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Require that the necessary roles already exist before creating users
        $requiredRoles = ['staff', 'department-head', 'verificator', 'general-manager', 'director', 'purchaser'];
        
        foreach ($requiredRoles as $role) {
            if (!Role::where('name', $role)->exists()) {
                if ($this->command) {
                    $this->command->warn("Role '{$role}' does not exist. Please run RolesAndPermissionsSeeder first.");
                }
                return;
            }
        }

        // 3. Create Test Users
        $this->createTestUser('staff', 'Requester User', 'requester@example.com', 'staff');
        $this->createTestUser('department-head', 'Dept Head User', 'head@example.com', 'department-head', 'MOULDING'); // Example Dept
        $this->createTestUser('verificator', 'Verificator User', 'verificator@example.com', 'verificator');
        $this->createTestUser('general-manager', 'GM User', 'gm@example.com', 'general-manager');
        $this->createTestUser('director', 'Director User', 'director@example.com', 'director');

        // Specific Purchasers for Filtering Test
        $this->createTestUser('purchaser', 'Purchaser IT', 'purchaser_it@example.com', 'purchaser', 'COMPUTER');
        $this->createTestUser('purchaser', 'Purchaser Maintenance', 'purchaser_main@example.com', 'purchaser', 'MAINTENANCE');
        $this->createTestUser('purchaser', 'Purchaser Purchasing', 'purchaser_purc@example.com', 'purchaser', 'PURCHASING');
        $this->createTestUser('purchaser', 'Purchaser Personalia', 'purchaser_hrd@example.com', 'purchaser', 'PERSONALIA');
        
        if ($this->command) {
            $this->command->info("Test PR Users seeded successfully.");
        }
    }

    private function createTestUser($roleName, $name, $email, $roleSlug, $deptName = null): void
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make('password'),
            ]
        );

        // Assign role if they don't have it
        if (!$user->hasRole($roleSlug)) {
            $user->assignRole($roleSlug);
        }

        // Assign Department if provided
        if ($deptName) {
            $dept = Department::where('name', $deptName)->first();
            if ($dept) {
                $user->department_id = $dept->id;
                $user->save();
            }
        }
    }
}
