<?php
// app/Support/ApprovalFlowResolver.php

namespace App\Support;

use App\Models\{HeaderFormOvertime, ApprovalFlowRule};
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ApprovalFlowResolver
{
    /**
     * Return the Flow slug for a given HeaderData array.
     */
    public static function for(array $header): string
    {
        // cache rules for 1 hour
        $rules = Cache::remember("approval_flow_rules", 3600, function () {
            return ApprovalFlowRule::with(["department", "flow"])
                ->orderBy("priority")
                ->get();
        });

        $deptId = $header["dept_id"] ?? null;
        $branch = Str::upper($header["branch"] ?? "");
        $isDesign = $header["is_design"];

        // first rule that matches wins
        foreach ($rules as $rule) {
            if (
                (!$rule->department_id || $rule->department_id == $deptId) &&
                (!$rule->branch || Str::upper($rule->branch) == $branch) &&
                ($rule->is_design === null || $rule->is_design == $isDesign)
            ) {
                return $rule->flow->slug; // ensure slug column exists on ApprovalFlow
            }
        }

        // fallback
        return "dept-head-director";
    }
}
