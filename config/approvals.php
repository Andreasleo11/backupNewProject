<?php

// config/approvals.php
return [

    /*
    |--------------------------------------------------------------------------
    | Registered Approvable Types
    |--------------------------------------------------------------------------
    */
    'approvables' => [
        \App\Infrastructure\Persistence\Eloquent\Models\VerificationReport::class => 'Verification Report',
        \App\Infrastructure\Persistence\Eloquent\Models\VerificationItem::class   => 'Verification Item',
        // add more…
    ],

    /*
    |--------------------------------------------------------------------------
    | Department Responsibility Links
    |--------------------------------------------------------------------------
    |
    | Defines cross-department responsibility chains for approval visibility
    | and notifications.
    |
    | When a user's primary department matches a key, they will ALSO have
    | oversight over all departments listed in the corresponding array.
    |
    | This is the single source of truth — read by ApprovalScopingManager,
    | all RoleScopingStrategy implementations, and any future modules.
    |
    | To add a new link, simply add an entry here. No PHP class changes needed.
    |
    */
    'department_links' => [
        'LOGISTIC' => ['STORE'],
        'QC'       => ['QA'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Jurisdiction-Scoped Roles
    |--------------------------------------------------------------------------
    |
    | Roles whose ACTIVE-TURN visibility must be intersected with their
    | geographic/departmental jurisdiction. A user with these roles cannot see
    | a pending item — even if the step is assigned to their role — unless the
    | item belongs to their branch or department.
    |
    | To add a new jurisdiction-scoped role, add its slug here.
    |
    */
    'jurisdiction_scoped_roles' => [
        'department-head',
        'supervisor',
        'general-manager',
    ],

];
