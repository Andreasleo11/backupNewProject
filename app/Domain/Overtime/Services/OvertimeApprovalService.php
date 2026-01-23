<?php

declare(strict_types=1);

namespace App\Domain\Overtime\Services;

use App\Models\HeaderFormOvertime;
use App\Models\OvertimeFormApproval;
use Illuminate\Support\Facades\Auth;

final class OvertimeApprovalService
{
    /**
     * Sign/approve an overtime form at a specific step.
     */
    public function sign(int $formId, int $stepId): array
    {
        $form = HeaderFormOvertime::find($formId);

        if (! $form) {
            return [
                'success' => false,
                'message' => 'Form not found',
            ];
        }

        $approval = $form->approvals()->where('flow_step_id', $stepId)->first();

        if (! $approval) {
            return [
                'success' => false,
                'message' => 'Approval step not found',
            ];
        }

        $username = Auth::user()->name;
        $imagePath = $username . '.png';

        $approval->update([
            'status' => 'approved',
            'signed_at' => now(),
            'approver_id' => auth()->id(),
            'signature_path' => $imagePath,
        ]);

        // Update form status based on approval flow
        $this->updateFormStatus($form);

        return [
            'success' => true,
            'message' => 'Form signed successfully',
        ];
    }

    /**
     * Reject an overtime form with a reason.
     */
    public function reject(int $formId, int $approvalId, string $description): array
    {
        $form = HeaderFormOvertime::find($formId);

        if (! $form) {
            return [
                'success' => false,
                'message' => 'Form not found',
            ];
        }

        $form->update([
            'description' => $description,
            'status' => 'rejected',
        ]);

        OvertimeFormApproval::find($approvalId)?->update(['status' => 'rejected']);

        return [
            'success' => true,
            'message' => 'Report rejected',
        ];
    }

    /**
     * Update form status based on current approval flow.
     */
    private function updateFormStatus(HeaderFormOvertime $form): void
    {
        if ($form->currentStep() === null) {
            $form->update(['status' => 'approved']);
        } elseif ($form->nextStep()) {
            $status = 'waiting-' . str_replace('_', '-', $form->nextStep()->role_slug);
            $form->update(['status' => $status]);
        } else {
            $form->update(['status' => 'Unknown']);
        }
    }

    /**
     * Reject a detail manually from server side.
     */
    public function rejectDetail(int $detailId): array
    {
        $detail = \App\Models\DetailFormOvertime::find($detailId);

        if (! $detail) {
            return [
                'success' => false,
                'message' => 'Detail not found',
            ];
        }

        $detail->update([
            'status' => 'Rejected',
            'reason' => 'Rejected manually from DISS server',
        ]);

        return [
            'success' => true,
            'message' => 'Detail rejected successfully',
        ];
    }
}
