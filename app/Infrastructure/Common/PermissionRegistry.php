<?php

namespace App\Infrastructure\Common;

/**
 * A centralized registry for modular permissions and role defaults.
 * This makes it easier to scale the application by registering new modules
 * without bloating a single seeder file.
 */
class PermissionRegistry
{
    /**
     * Get all registered modules.
     * In the future, this can dynamically load from module config files.
     */
    public static function getModules(): array
    {
        return [
            'System' => [
                'permissions' => [
                    'user.view-any', 'user.view', 'user.create', 'user.update', 'user.delete', 'user.change-password',
                    'role.view-any', 'role.create', 'role.update', 'role.delete', 'permission.view-any',
                    'department.view-any', 'department.create', 'department.update', 'department.delete',
                    'employee.view-any', 'employee.view', 'employee.update',
                    'system.admin', // Global bypass
                    'dashboard.view', // Basic access to dashboard
                ],
                'roles' => [
                    'super-admin' => ['*'],
                    'staff' => ['dashboard.view'],
                    'department-head' => ['dashboard.view'],
                    'general-manager' => ['dashboard.view'],
                    'verificator' => ['dashboard.view'],
                    'director' => ['dashboard.view'],
                ],
            ],
            'Purchase Request' => [
                'permissions' => [
                    'pr.view', 'pr.view-all', 'pr.create', 'pr.edit', 'pr.delete', 'pr.delete-forever', 'pr.cancel', 'pr.print', 'pr.batch-approve', 'pr.approve', 'pr.reject',
                    'pr.auto-approve', // Replacement for GM/Moulding logic
                    'pr.view-prices', // Capability to see sensitive pricing data
                    'pr.admin', // Full bypass for PR logic
                ],
                'roles' => [
                    'staff' => ['pr.view', 'pr.create', 'pr.edit', 'pr.delete', 'pr.cancel', 'pr.print'],
                    'department-head' => ['pr.view', 'pr.create', 'pr.edit', 'pr.cancel', 'pr.print', 'pr.approve', 'pr.reject', 'pr.batch-approve', 'pr.view-prices'],
                    'verificator' => ['pr.view', 'pr.print', 'pr.approve', 'pr.reject', 'pr.batch-approve', 'pr.view-prices'],
                    'general-manager' => ['pr.view', 'pr.print', 'pr.approve', 'pr.reject', 'pr.auto-approve', 'pr.batch-approve', 'pr.view-prices'],
                    'director' => ['pr.view', 'pr.print', 'pr.approve', 'pr.reject', 'pr.batch-approve', 'pr.auto-approve', 'pr.view-prices'],
                    'purchaser' => ['pr.view', 'pr.create', 'pr.edit', 'pr.cancel', 'pr.print', 'pr.approve', 'pr.auto-approve', 'pr.view-prices'],
                    'purchasing-manager' => ['pr.view', 'pr.batch-approve', 'approval.view-log', 'pr.admin', 'pr.delete-forever', 'pr.view-prices'],
                    'accounting-officer' => ['pr.view', 'pr.print', 'pr.view-prices'],
                ],
            ],
            'Approval Engine' => [
                'permissions' => [
                    'approval.view-log', 'approval.manage-rules',
                ],
            ],
            'Evaluation & Discipline' => [
                'permissions' => [
                    'evaluation.view-any', 'evaluation.view-department', 'evaluation.grade', 'evaluation.approve-department', 'evaluation.approve-final', 'evaluation.view-regular', 'evaluation.view-yayasan', 'evaluation.view-magang', 'evaluation.export-jpayroll',
                ],
                'roles' => [
                    'department-head' => ['evaluation.view-department', 'evaluation.grade', 'evaluation.approve-department', 'evaluation.view-regular', 'evaluation.view-yayasan', 'evaluation.view-magang'],
                    'hrd-manager' => ['evaluation.view-any', 'evaluation.approve-final', 'evaluation.view-regular', 'evaluation.view-yayasan', 'evaluation.view-magang'],
                    'general-manager' => ['evaluation.view-any', 'evaluation.approve-final', 'evaluation.view-regular', 'evaluation.view-yayasan', 'evaluation.view-magang'],
                ],
            ],
            'Overtime' => [
                'permissions' => [
                    'overtime.view', 'overtime.view-all', 'overtime.create', 'overtime.delete', 'overtime.export', 'overtime.review', 'overtime.approve', 'overtime.push-to-payroll',
                ],
                'roles' => [
                    'verificator' => ['overtime.review', 'overtime.approve', 'overtime.export', 'overtime.delete', 'overtime.push-to-payroll'],
                    'director' => ['overtime.view', 'overtime.approve', 'overtime.export'],
                    'general-manager' => ['overtime.view', 'overtime.approve', 'overtime.export'],
                    'department-head' => ['overtime.view', 'overtime.approve'],
                    'staff' => ['overtime.view', 'overtime.create'],
                ],
            ],
            'Inventory & Assets' => [
                'permissions' => [
                    'inventory.view', 'inventory.manage', 'inventory.view-maintenance',
                ],
                'roles' => [
                    'inventory' => ['inventory.view', 'inventory.manage', 'inventory.view-maintenance'],
                    'operations' => ['inventory.view'],
                    'manager' => ['inventory.view', 'inventory.view-maintenance'],
                ],
            ],
            'Quality Control' => [
                'permissions' => [
                    'qc.view', 'qc.manage',
                ],
                'roles' => [
                    'quality' => ['qc.view', 'qc.manage'],
                    'operations' => ['qc.view'],
                    'manager' => ['qc.view'],
                ],
            ],
            'Production' => [
                'permissions' => [
                    'production.view', 'production.manage',
                ],
                'roles' => [
                    'production' => ['production.view', 'production.manage'],
                    'operations' => ['production.view'],
                ],
            ],
            'Procurement Extras' => [
                'permissions' => [
                    'po.view-any', 'po.manage', 'purchasing.view', 'purchasing.forecast',
                ],
                'roles' => [
                    'po.admin' => ['po.view-any', 'po.manage'],
                    'purchasing' => ['purchasing.view', 'purchasing.forecast'],
                ],
            ],
            'Finance & Budget' => [
                'permissions' => [
                    'budget.view', 'budget.manage', 'expense.view',
                ],
                'roles' => [
                    'finance' => ['budget.view', 'budget.manage', 'expense.view'],
                    'accounting' => ['budget.view', 'expense.view'],
                    'manager' => ['budget.view', 'expense.view'],
                ],
            ],
            'Operations' => [
                'permissions' => [
                    'ops.view', 'ops.manage', 'spk.view', 'spk.manage',
                ],
                'roles' => [
                    'operations' => ['ops.view', 'ops.manage', 'spk.view', 'spk.manage'],
                    'logistics' => ['ops.view', 'ops.manage'],
                    'production' => ['spk.view'],
                    'manager' => ['ops.view', 'spk.view'],
                ],
            ],
            'Personnel' => [
                'permissions' => [
                    'personnel.view', 'personnel.manage', 'training.view', 'training.manage', 'document.view', 'document.manage',
                ],
                'roles' => [
                    'hr' => ['personnel.view', 'personnel.manage', 'training.view', 'training.manage', 'document.view', 'document.manage'],
                    'hrd' => ['personnel.view', 'personnel.manage', 'training.view', 'training.manage', 'document.view', 'document.manage'],
                    'manager' => ['personnel.view', 'training.view', 'document.view'],
                ],
            ],
            'Compliance' => [
                'permissions' => [
                    'compliance.view', 'compliance.manage', 'compliance.review-uploads',
                ],
                'roles' => [
                    'compliance' => ['compliance.view', 'compliance.manage', 'compliance.review-uploads'],
                    'hr' => ['compliance.view', 'compliance.manage'],
                    'manager' => ['compliance.view'],
                ],
            ],
        ];
    }

    /**
     * Fetch a flat list of all permissions across all modules.
     */
    public static function allPermissions(): array
    {
        $all = [];
        foreach (self::getModules() as $module => $data) {
            if (isset($data['permissions'])) {
                $all = array_merge($all, $data['permissions']);
            }
        }

        return array_values(array_unique($all));
    }

    /**
     * Fetch a consolidated mapping of role => [permissions].
     */
    public static function allRolesWithPermissions(): array
    {
        $all = [];
        foreach (self::getModules() as $module => $data) {
            foreach ($data['roles'] ?? [] as $role => $perms) {
                if (! isset($all[$role])) {
                    $all[$role] = [];
                }
                $all[$role] = array_merge($all[$role], $perms);
            }
        }

        // Clean up duplicates per role
        foreach ($all as $role => $perms) {
            $all[$role] = array_values(array_unique($perms));
        }

        return $all;
    }

    /**
     * Get a list of permissions that inherently require a digital signature (approvals/creations).
     */
    public static function getSignatureRequiredPermissions(): array
    {
        return array_values(array_filter(self::allPermissions(), function ($p) {
            return str_contains($p, '.approve') ||
                str_contains($p, '.reject') ||
                str_contains($p, '.review') ||
                $p === 'pr.create' ||
                $p === 'overtime.create' ||
                str_contains($p, '.approve-items');
        }));
    }
}
