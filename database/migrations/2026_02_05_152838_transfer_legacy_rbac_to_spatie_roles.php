<?php

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure all target roles exist
        // We use lowercase (new standard from PermissionRegistry) for consistent naming.
        $lowercaseModern = [
            'inspector', 'leader', 'staff', 'director', 'admin', 'department-head',
            'purchaser', 'verificator', 'design', 'supervisor',
        ];

        $allRoles = array_unique(array_merge(
            ['super-admin', 'staff', 'user'],
            $lowercaseModern
        ));

        foreach ($allRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // 2. Map legacy role_id (from users table)
        // Note: we use raw query or check if column exists because it might be dropped in next migrations
        if (Schema::hasColumn('users', 'role_id')) {
            $usersWithLegacyRole = User::whereNotNull('role_id')->get();
            foreach ($usersWithLegacyRole as $user) {
                $roleName = match ((int) $user->role_id) {
                    1 => 'super-admin',
                    2 => 'staff',
                    3 => 'user',
                    default => null
                };

                if ($roleName && ! $user->hasRole($roleName)) {
                    $user->assignRole($roleName);
                }
            }
        }

        // 3. Map legacy specification_id (from users table)
        if (Schema::hasColumn('users', 'specification_id')) {
            $usersWithSpec = User::whereNotNull('specification_id')->get();
            foreach ($usersWithSpec as $user) {
                $specRole = match ((int) $user->specification_id) {
                    2 => 'inspector',
                    3 => 'leader',
                    4 => 'staff',
                    5 => 'director',
                    6 => 'admin',
                    7 => 'department-head',
                    14 => 'purchaser',
                    15 => 'verificator',
                    16 => 'design',
                    17 => 'supervisor',
                    default => null
                };

                if ($specRole && ! $user->hasRole($specRole)) {
                    $user->assignRole($specRole);
                }
            }
        }
    }

    /**
     * Reverse the migrations (Cannot easily reverse role assignments without knowing previous state,
     * but we can leave the roles assigned as they don't break legacy system).
     */
    public function down(): void
    {
        // No-op for safety
    }
};
