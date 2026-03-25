<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Permission Group Definitions
    |--------------------------------------------------------------------------
    |
    | Maps human-readable module labels to their permission prefix(es).
    | Any permission whose name starts with the given prefix is automatically
    | placed in that group — no code changes needed when adding new permissions.
    |
    | To add a new module: add a single entry here, seed the new permissions,
    | and they will appear in the Role and User management UIs automatically.
    |
    | Order of this array controls the display order in the admin UI.
    |
    */
    'groups' => [
        'Evaluation & Discipline' => ['evaluation.'],
        'Purchase Request'        => ['pr.'],
        'Overtime'                => ['overtime.'],
        'Approval Engine'         => ['approval.'],
        'User Management'         => ['user.'],
        'Roles & Permissions'     => ['role.', 'permission.'],
        'Departments'             => ['department.'],
        'Employees'               => ['employee.'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Descriptions
    |--------------------------------------------------------------------------
    |
    | Human-readable tooltips shown in the User Management UI when hovering
    | over a role tag. Add a new role here when creating it in the seeder.
    |
    */
    'role_descriptions' => [
        'super-admin'     => 'Full system access — all permissions',
        'department-head' => 'Grade evaluations, view Overtime forms, and approve PRs at department level.',
        'hrd-manager'     => 'Final-approve evaluations, view all eval tabs company-wide',
        'general-manager' => 'Final-approve evaluations, view all Overtime forms, and approve PRs at GM level.',
        'verificator'     => 'Final review for Overtime forms and PR verification.',
        'director'        => 'Company-wide PR and Overtime approval authority (Final step).',
        'staff'           => 'Submit, edit, and cancel Purchase Requests and Overtime forms.',
        'purchaser'       => 'Process approved PRs for purchasing and manage POs.',
        'purchasing-manager' => 'Manage PR approval rules and view all logs',
        'accounting-officer' => 'Review and process accounting aspects of PRs and Overtime.',
    ],

];
