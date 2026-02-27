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
        $requiredRoles = ['requester', 'pr-dept-head', 'pr-verificator', 'pr-gm', 'pr-director', 'pr-purchaser'];
        
        foreach ($requiredRoles as $role) {
            if (!Role::where('name', $role)->exists()) {
                if ($this->command) {
                    $this->command->warn("Role '{$role}' does not exist. Please run RolesAndPermissionsSeeder first.");
                }
                return;
            }
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
