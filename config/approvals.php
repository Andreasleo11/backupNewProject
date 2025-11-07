<?php

// config/approvals.php
return [
    'approvables' => [
        \App\Infrastructure\Persistence\Eloquent\Models\VerificationReport::class => 'Verification Report',
        \App\Infrastructure\Persistence\Eloquent\Models\VerificationItem::class => 'Verification Item',
        // add more…
    ],
];
