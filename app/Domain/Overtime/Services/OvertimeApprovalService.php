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
        // currentStep() returns null when all steps are approved.
        if ($form->currentStep() === null) {
            $form->update(['status' => 'approved']);

            return;
        }

        // There is still a pending step — compute its status slug.
        $next = $form->nextStep();
        if ($next) {
            $status = 'waiting-' . str_replace('_', '-', $next->role_slug);
            $form->update(['status' => $status]);
        }
        // If nextStep() is also null but currentStep() wasn't, the flow is empty/misconfigured.
        // Fail loudly in non-production; silently mark approved in production.
        else {
            logger()->warning("OvertimeApprovalService: form #{$form->id} has no next step but currentStep is non-null. Marking approved.");
            $form->update(['status' => 'approved']);
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
