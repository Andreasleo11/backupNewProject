<?php
// app/Helpers/helpers.php

use Illuminate\Support\Str;

if (! function_exists('pickApprovalFlowSlug')) {
    function pickApprovalFlowSlug(\App\Models\HeaderFormOvertime $header): string
    {
        $dept     = Str::upper($header->department->name ?? '');
        $branch   = Str::upper($header->branch ?? '');
        $isDesign = (bool) $header->is_design;

        return match (true) {
            // in_array($dept, ['ASSEMBLY', 'SECOND PROCESS', 'STORE', 'LOGISTIC']) =>
            // 'dept-head-director',

            // ――― MOULDING ―――
            $dept === 'MOULDING' && ! $isDesign && $branch === 'KARAWANG' =>
            'gm-director',

            $dept === 'MOULDING' && ! $isDesign =>
            'supervisor-dept-head-director',

            // ――― PPIC ―――
            $dept === 'PPIC' => 'dept-head-gm-director',

            // ――― PLASTIC INJECTION ―――
            $dept === 'PLASTIC INJECTION' && $branch === 'KARAWANG' =>
            'gm-director',

            $dept === 'PLASTIC INJECTION' =>
            'dept-head-gm-director',

            $dept === 'MAINTENANCE MACHINE' => 'dept-head-gm-director',

            // fallback
            default => 'dept-head-director',
        };
    }
}
