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
                    'pr.view', 'pr.view-all', 'pr.create', 'pr.edit', 'pr.delete', 'pr.cancel', 'pr.upload-files', 'pr.print', 'pr.batch-approve',
                ],
                'roles' => [
                    'staff'              => ['pr.view', 'pr.create', 'pr.edit', 'pr.delete', 'pr.cancel', 'pr.upload-files', 'pr.print'],
                    'department-head'    => ['pr.view', 'pr.view-all', 'pr.create', 'pr.edit', 'pr.cancel', 'pr.upload-files', 'pr.print', 'approval.approve', 'approval.reject', 'approval.approve-items'],
                    'verificator'        => ['pr.view', 'pr.view-all', 'pr.print', 'approval.approve', 'approval.reject', 'approval.approve-items'],
                    'general-manager'    => ['pr.view', 'pr.view-all', 'pr.print', 'approval.approve', 'approval.reject', 'approval.approve-items'],
                    'director'           => ['pr.view', 'pr.view-all', 'pr.print', 'approval.approve', 'approval.reject', 'approval.approve-items', 'pr.batch-approve'],
                    'purchaser'          => ['pr.view', 'pr.view-all', 'pr.create', 'pr.edit', 'pr.cancel', 'pr.upload-files', 'pr.print', 'approval.approve'],
                    'purchasing-manager' => ['pr.view-all', 'pr.batch-approve', 'approval.view-log'],
                    'accounting-officer' => ['pr.view', 'pr.view-all', 'pr.print'],
                ],
            ],
            'Approval Engine' => [
                'permissions' => [
                    'approval.view-log', 'approval.manage-rules', 'approval.approve', 'approval.reject', 'approval.approve-items',
                ],
                'roles' => [
                    'director' => ['pr.view-all', 'approval.approve', 'approval.reject', 'approval.approve-items', 'pr.batch-approve', 'approval.view-log'],
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
                    'verificator'     => ['overtime.view-all', 'overtime.review', 'overtime.approve', 'overtime.export', 'overtime.delete', 'overtime.push-to-payroll'],
                    'director'        => ['overtime.view-all', 'overtime.approve', 'overtime.export'],
                    'general-manager' => ['overtime.view-all', 'overtime.approve', 'overtime.export'],
                    'department-head' => ['overtime.view-all', 'overtime.approve'],
                    'staff'           => ['overtime.view', 'overtime.create'],
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
}
