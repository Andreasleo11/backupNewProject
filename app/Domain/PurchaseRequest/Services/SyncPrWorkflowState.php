<?php

namespace App\Domain\PurchaseRequest\Services;

use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Models\PurchaseRequest;
use Spatie\Permission\Models\Role;

class SyncPrWorkflowState
{
    public function sync(PurchaseRequest $pr, ?ApprovalRequest $approval): void
    {
        // if cancelled, don't override (UI already uses is_cancel)
        if ((int) $pr->is_cancel === 1) {
            $pr->workflow_status = 'CANCELED';
            $pr->workflow_step = null;
            $pr->save();

            return;
        }

        if (! $approval) {
            // if no workflow exists, keep null or mark DRAFT (your choice)
            // safest: only set if empty
            if (! $pr->workflow_status) {
                $pr->workflow_status = 'DRAFT';
                $pr->workflow_step = null;
                $pr->save();
            }

            return;
        }

        $workflowStatus = strtoupper(trim((string) $approval->status));
        $pr->workflow_status = $workflowStatus;

        // Default: no step label unless IN_REVIEW
        $pr->workflow_step = null;

        if ($workflowStatus === 'IN_REVIEW') {
            $step = $approval->steps?->firstWhere('sequence', (int) $approval->current_step);

            if ($step) {
                // Prefer snapshot if you have it
                $roleName = $step->approver_snapshot_name ?? null;

                // If not available, derive from role/user (role is typical for approval)
                if (! $roleName && $step->approver_type === 'role') {
                    $roleName = Role::find($step->approver_id)?->name;
                }

                // Keep as stored value (pr-director etc)
                $pr->workflow_step = $roleName ? trim($roleName) : null;
            }
        }

        $pr->save();
    }
}
