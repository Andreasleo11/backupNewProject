<?php

namespace App\Application\PurchaseRequest\Services;

use App\Application\PurchaseRequest\Contracts\SyncPrWorkflow;
use App\Models\PurchaseRequest;

final class SyncPrWorkflowFromApproval implements SyncPrWorkflow
{
    public function sync(PurchaseRequest $pr): void
    {
        // if cancelled, don't override
        if ((int) $pr->is_cancel === 1) {
            $pr->workflow_status = 'CANCELED';
            $pr->workflow_step = null;
            $pr->save();

            return;
        }

        $approval = $pr->approvalRequest;

        if (!$approval) {
            // no workflow => draft / null
            $pr->workflow_status = $pr->workflow_status ?: 'DRAFT';
            $pr->workflow_step = null;
            $pr->save();

            return;
        }

        $ws = strtoupper(trim((string) $approval->status));
        $pr->workflow_status = $ws;

        $pr->workflow_step = null;

        if ($ws === 'IN_REVIEW') {
            $steps = $approval->steps?->sortBy('sequence');
            $current = $steps?->firstWhere('sequence', (int) $approval->current_step);

            if ($current) {
                // if you store role name snapshot, prefer it
                $pr->workflow_step = $current->approver_snapshot_name
                    ?? $current->approver_snapshot_label
                    ?? null;

                // fallback: store something stable
                if (!$pr->workflow_step) {
                    $pr->workflow_step = $current->approver_type . ':' . $current->approver_id;
                }
            }
        }

        $pr->save();
    }
}
