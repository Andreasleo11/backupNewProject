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
     * 
     * @return array
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
                ],
                'roles' => [
                    'super-admin' => ['*'],
                ],
            ],
            'Purchase Request' => [
                'permissions' => [
                    'pr.view', 'pr.view-any', 'pr.view-all', 'pr.create', 'pr.edit', 'pr.delete', 'pr.cancel', 'pr.upload-files', 'pr.print', 'pr.batch-approve', 'pr.approve', 'pr.reject', 'pr.approve-items',
                ],
                'roles' => [
                    'staff'              => ['pr.view', 'pr.view-any', 'pr.create', 'pr.edit', 'pr.delete', 'pr.cancel', 'pr.upload-files', 'pr.print'],
                    'department-head'    => ['pr.view', 'pr.view-any', 'pr.view-all', 'pr.create', 'pr.edit', 'pr.cancel', 'pr.upload-files', 'pr.print', 'pr.approve', 'pr.reject', 'pr.approve-items'],
                    'verificator'        => ['pr.view', 'pr.view-any', 'pr.view-all', 'pr.print', 'pr.approve', 'pr.reject', 'pr.approve-items'],
                    'general-manager'    => ['pr.view', 'pr.view-any', 'pr.view-all', 'pr.print', 'pr.approve', 'pr.reject', 'pr.approve-items'],
                    'director'           => ['pr.view', 'pr.view-any', 'pr.view-all', 'pr.print', 'pr.approve', 'pr.reject', 'pr.approve-items', 'pr.batch-approve'],
                    'purchaser'          => ['pr.view', 'pr.view-any', 'pr.view-all', 'pr.create', 'pr.edit', 'pr.cancel', 'pr.upload-files', 'pr.print', 'pr.approve'],
                    'purchasing-manager' => ['pr.view-any', 'pr.view-all', 'pr.batch-approve', 'approval.view-log'],
                    'accounting-officer' => ['pr.view', 'pr.view-any', 'pr.view-all', 'pr.print'],
                ],
            ],
            'Approval Engine' => [
                'permissions' => [
                    'approval.view-log', 'approval.manage-rules',
                ]
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
                    'verificator'     => ['overtime.view-all', 'overtime.review', 'overtime.approve', 'overtime.export', 'overtime.delete', 'overtime.push-to-payroll'],
                    'director'        => ['overtime.view-all', 'overtime.approve', 'overtime.export'],
                    'general-manager' => ['overtime.view-all', 'overtime.approve', 'overtime.export'],
                    'department-head' => ['overtime.view-all', 'overtime.approve'],
                    'staff'           => ['overtime.view', 'overtime.create'],
                ],
            ],
            'Inventory & Assets' => [
                'permissions' => [
                    'inventory.view', 'inventory.manage',
                ],
                'roles' => [
                    'inventory' => ['inventory.view', 'inventory.manage'],
                    'operations' => ['inventory.view'],
                    'manager' => ['inventory.view'],
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
                    'procurement' => ['po.view-any', 'po.manage', 'purchasing.view', 'purchasing.forecast'],
                    'sales' => ['purchasing.forecast'],
                    'manager' => ['po.view-any', 'purchasing.view'],
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
     * 
     * @return array
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
     * 
     * @return array
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
     * 
     * @return array
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
