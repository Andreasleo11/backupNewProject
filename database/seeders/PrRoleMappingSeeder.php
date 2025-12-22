<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PrRoleMappingSeeder extends Seeder
{
    public function run(): void
    {
        // Refresh cache dulu
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard', 'web');

        // Pastikan permission dasar untuk PR sudah ada (dibuat di RolesAndPermissionsSeeder)
        $permNames = [
            'pr.view-own',
            'pr.view-dept',
            'pr.view-any',
            'pr.create',
            'pr.update',
            'pr.cancel',
            'pr.submit',
            'pr.approve',
            'approval.view-log',
        ];

        $perms = Permission::whereIn('name', $permNames)
            ->where('guard_name', $guard)
            ->get()
            ->keyBy('name');

        // Helper
        $getPerms = fn(array $names) => $perms->only($names)->values();

        // ===== PR approval roles (generic, dipakai di ApprovalEngine) =====
        $roleSpecs = [
            // Maker tidak dipakai sebagai step engine, tapi bisa dipakai kalau nanti perlu
            'pr-maker' => [
                'pr.view-own',
                'pr.create',
                'pr.update',
                'pr.submit',
                'pr.cancel',
            ],

            // Dept head (office side) -> step: DEPT_HEAD
            'pr-dept-head-office' => [
                'pr.view-dept',
                'pr.view-own',
                'pr.approve',
                'approval.view-log',
            ],

            // Dept head (factory side)
            'pr-dept-head-factory' => [
                'pr.view-dept',
                'pr.view-own',
                'pr.approve',
                'approval.view-log',
            ],

            // Head Design (khusus MOULDING is_design)
            'pr-head-design' => [
                'pr.view-dept',
                'pr.view-own',
                'pr.approve',
                'approval.view-log',
            ],

            // GM untuk factory departments
            'pr-gm-factory' => [
                'pr.view-dept',
                'pr.view-any',
                'pr.approve',
                'approval.view-log',
            ],

            // Verificator PERSONALIA (to_department=PERSONALIA)
            'pr-verificator-personalia' => [
                'pr.view-dept',
                'pr.view-any',
                'pr.approve',
                'approval.view-log',
            ],

            // Verificator COMPUTER (to_department=COMPUTER)
            'pr-verificator-computer' => [
                'pr.view-dept',
                'pr.view-any',
                'pr.approve',
                'approval.view-log',
            ],

            // Purchaser
            'pr-purchaser' => [
                'pr.view-dept',
                'pr.view-any',
                'pr.approve',
                'approval.view-log',
            ],

            // Director (final approver)
            'pr-director' => [
                'pr.view-any',
                'pr.approve',
                'approval.view-log',
            ],
        ];

        foreach ($roleSpecs as $roleName => $permList) {
            /** @var Role $role */
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => $guard],
                []
            );

            $rolePerms = $getPerms($permList);

            if ($rolePerms->isNotEmpty()) {
                $role->syncPermissions($rolePerms);
            }
        }

        // Refresh cache lagi setelah assign
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /*
         * NOTE:
         *  - Di sini kita tidak assign role ke user mana pun.
         *  - Mapping real:
         *      - Director -> role "director" + "pr-director"
         *      - GM factory -> "general-manager-*"+ "pr-gm-factory"
         *      - Dept head office (ACCOUNTING, MANAGEMENT, ...) -> "pr-dept-head-office"
         *      - Dept head factory (MOULDING, QA, QC, ...) -> "pr-dept-head-factory"
         *      - Head Design -> "pr-head-design"
         *      - Verificator personalia -> "pr-verificator-personalia"
         *      - Verificator computer -> "pr-verificator-computer"
         *      - Tim purchaser -> "pr-purchaser"
         *
         *  Mapping ini bisa kamu lakukan via UI atau seeder terpisah.
         */
    }
}
