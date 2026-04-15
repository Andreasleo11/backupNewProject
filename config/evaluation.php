<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Discipline Access Control
    |--------------------------------------------------------------------------
    |
    | Centralised list of users/emails with elevated access to the
    | Performance & Evaluation discipline module.
    |
    | Move any future user-specific overrides here instead of hardcoding
    | names or IDs inside service classes or controllers.
    |
    */

    /**
     * Users who can approve as HRD (the final Yayasan approval step).
     * These users see all Yayasan employees across all departments.
     */
    'hrd_approvers' => [
        'bernadett@daijo.co.id',
    ],

    /**
     * Users who have super-access to view/manage all discipline records
     * (equivalent to a cross-department HR manager).
     */
    'super_access_emails' => [
        'ani_apriani@daijo.co.id',
        'bernadett@daijo.co.id',
    ],

    /**
     * Legacy user IDs with special cross-department access.
     * Prefer email-based config above — this is for backwards compatibility.
     */
    'special_access_ids' => [
        120, // QC+QA combined access user
    ],

    /*
    |--------------------------------------------------------------------------
    | Combined Department Head Mappings
    |--------------------------------------------------------------------------
    |
    | Some department heads manage employees across two departments.
    | Format: 'username' => ['dept_code_1', 'dept_code_2']
    |
    | When resolving which evaluation records a user can see, these mappings
    | are checked BEFORE the standard single-department lookup.
    |
    */
    'combined_dept_heads' => [
        // 'popon' sees Second Process (361) + Assembly (362)
        'popon' => ['361', '362'],
        // 'catur' sees Store (330) + Logistic (331)
        'catur' => ['330', '331'],
        // 'yuli' sees QC (340) + QA (341)
        'yuli' => ['340', '341'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Scoring System Configuration
    |--------------------------------------------------------------------------
    |
    | Which employment types use which scoring system.
    | 'new' = 9-field A–E grading (Yayasan, Magang)
    | 'old' = 5-field A–E grading with base-40 attendance score (Regular)
    |
    */
    'scoring_system' => [
        'YAYASAN' => 'new',
        'YAYASAN KARAWANG' => 'new',
        'MAGANG' => 'new',
        'KONTRAK' => 'old',
        'TETAP' => 'old',
    ],

];
