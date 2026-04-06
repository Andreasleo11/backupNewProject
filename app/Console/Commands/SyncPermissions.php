<?php

namespace App\Console\Commands;

use App\Infrastructure\Common\PermissionRegistry;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync {--force : Force the sync without asking}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize Spatie permissions and roles based on the PermissionRegistry';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Permission Sync...');

        // 1. Clear Cached Permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Create/Update Permissions
        $permissions = PermissionRegistry::allPermissions();
        $this->info('Syncing ' . count($permissions) . ' permissions...');
        
        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        // 3. Sync Roles
        $rolesWithPerms = PermissionRegistry::allRolesWithPermissions();
        $this->info('Syncing ' . count($rolesWithPerms) . ' roles...');

        foreach ($rolesWithPerms as $roleName => $perms) {
            $role = Role::findOrCreate($roleName, 'web');
            
            // If the role has '*', it gets all permissions
            if (in_array('*', $perms, true)) {
                $role->syncPermissions(Permission::all());
                $this->line(" - Role [{$roleName}]: Assigned All Permissions (*)");
            } else {
                $role->syncPermissions($perms);
                $this->line(" - Role [{$roleName}]: Assigned " . count($perms) . " permissions");
            }
        }

        // 4. Special Case: Purchaser Sub-roles
        // These roles are often variants of 'purchaser' and should inherit its core permissions
        $purchaserPerms = $rolesWithPerms['purchaser'] ?? [];
        $subPurchaserRoles = Role::where('name', 'like', 'purchaser-%')->get();
        
        foreach ($subPurchaserRoles as $subRole) {
            $subRole->syncPermissions($purchaserPerms);
            $this->line(" - Sub-Role [{$subRole->name}]: Synced with 'purchaser' permissions");
        }

        $this->info('Permission Sync Completed Successfully!');
        
        return self::SUCCESS;
    }
}
