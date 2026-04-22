<?php

namespace App\Console\Commands;

use App\Domain\Admin\Services\PermissionAuditService;
use App\Infrastructure\Common\PermissionRegistry;
use App\Models\PermissionSyncLog;
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
    protected $signature = 'permissions:sync 
                            {--force : Force the sync without asking}
                            {--preview : Show what would change without applying}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize Spatie permissions and roles based on the PermissionRegistry';

    /**
     * Execute the console command.
     */
    public function handle(PermissionAuditService $auditService)
    {
        $isPreview = $this->option('preview');

        if ($isPreview) {
            $this->info('--- PREVIEW MODE: No changes will be saved ---');
            $data = $auditService->getSyncState();
            $managed = $data['managed'];

            if (empty($managed)) {
                $this->info('Managed roles are already in sync with the database.');
            } else {
                foreach ($managed as $role => $diff) {
                    $this->line("<options=bold>Role: [{$role}]</>");
                    foreach ($diff['added'] as $p) {
                        $this->line(" <fg=green>+ {$p}</>");
                    }
                    foreach ($diff['removed'] as $p) {
                        $this->line(" <fg=red>- {$p}</>");
                    }
                }
            }

            if (! empty($data['unmanaged'])) {
                $this->warn("\n--- Unmanaged Roles (Database Only) ---");
                foreach (array_keys($data['unmanaged']) as $role) {
                    $this->line(" - {$role}");
                }
            }

            return self::SUCCESS;
        }

        $this->info('Starting Permission Sync...');

        // Capture state before
        $beforeState = $auditService->getCurrentState();

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
                $this->line(" - Role [{$roleName}]: Assigned " . count($perms) . ' permissions');
            }
        }

        // 4. Special Case: Purchaser Sub-roles
        $purchaserPerms = $rolesWithPerms['purchaser'] ?? [];
        $subPurchaserRoles = Role::where('name', 'like', 'purchaser-%')->get();

        foreach ($subPurchaserRoles as $subRole) {
            $subRole->syncPermissions($purchaserPerms);
            $this->line(" - Sub-Role [{$subRole->name}]: Synced with 'purchaser' permissions");
        }

        // Capture state after and log
        $afterState = $auditService->getCurrentState();
        $changes = $auditService->calculateDiff($beforeState, $afterState);

        if (! empty($changes)) {
            PermissionSyncLog::create([
                'user_id' => null, // CLI
                'snapshot' => $beforeState,
                'after_snapshot' => $afterState,
                'changes' => $changes,
                'description' => 'Synchronized via CLI command',
            ]);
            $this->info('Created sync log entry.');
        } else {
            $this->info('No changes detected; no log entry created.');
        }

        $this->info('Permission Sync Completed Successfully!');

        return self::SUCCESS;
    }
}
