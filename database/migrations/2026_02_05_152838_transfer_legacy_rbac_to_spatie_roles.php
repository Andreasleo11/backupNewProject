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
        $roles = [
            'super-admin', 'staff', 'user',
            'INSPECTOR', 'LEADER', 'STAFF', 'DIRECTOR', 'ADMIN', 'HEAD', 'PURCHASER', 'VERIFICATOR', 'DESIGN', 'SUPERVISOR',
        ];

        foreach ($roles as $roleName) {
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
                    2 => 'INSPECTOR',
                    3 => 'LEADER',
                    4 => 'STAFF',
                    5 => 'DIRECTOR',
                    6 => 'ADMIN',
                    7 => 'HEAD',
                    14 => 'PURCHASER',
                    15 => 'VERIFICATOR',
                    16 => 'DESIGN',
                    17 => 'SUPERVISOR',
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
