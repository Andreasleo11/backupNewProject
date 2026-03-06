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
        'department-head' => 'Grade evaluations, dept-approve, view all 3 eval tabs for their dept',
        'hrd-manager'     => 'Final-approve evaluations, view all eval tabs company-wide',
        'general-manager' => 'Final-approve evaluations, view all eval tabs company-wide',
        'requester'       => 'Submit, edit, and cancel Purchase Requests',
        'pr-dept-head'    => 'Approve/reject PRs at department level',
        'pr-verificator'  => 'Verify PRs before GM approval',
        'pr-gm'           => 'Approve PRs at GM level',
        'pr-director'     => 'Final PR approval + batch approve',
        'pr-purchaser'    => 'Process approved PRs for purchasing',
        'pr-admin'        => 'Manage PR approval rules and view all logs',
        'director'        => 'Company-wide PR approval authority',
    ],

];
