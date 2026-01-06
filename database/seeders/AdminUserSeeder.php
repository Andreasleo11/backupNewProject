<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create SUPERADMIN role if it doesn't exist
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'web'
        ]);

        // Create admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@daijo.co.id'],
            [
                'name' => 'Super Administrator',
                'password' => bcrypt('Admin1234'),
                'is_active' => true,
            ]
        );

        // Assign SUPERADMIN role
        if (!$admin->hasRole('super-admin')) {
            $admin->assignRole('super-admin');
        }

        $this->command->info('✅ Admin user created: admin@daijo.co.id / Admin1234');
    }
}
