<?php

namespace Tests\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Test Role Seeder
 * 
 * Creates consistent role IDs for testing approval workflows.
 * Using high IDs (100+) to avoid conflicts with production data.
 */
class TestRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // PR Approval Roles
        $roles = [
            ['id' => 100, 'name' => 'pr-dept-head', 'guard_name' => 'web'],
            ['id' => 102, 'name' => 'pr-verificator', 'guard_name' => 'web'],
            ['id' => 104, 'name' => 'pr-gm', 'guard_name' => 'web'],
            ['id' => 105, 'name' => 'pr-director', 'guard_name' => 'web'],
        ];

        foreach ($roles as $roleData) {
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }

    /**
     * Get role ID by name for tests
     */
    public static function getRoleId(string $roleName): int
    {
        $map = [
            'pr-dept-head' => 100,
            'pr-verificator' => 102,
            'pr-gm' => 104,
            'pr-director' => 105,
        ];

        return $map[$roleName] ?? throw new \InvalidArgumentException("Unknown role: $roleName");
    }
}
