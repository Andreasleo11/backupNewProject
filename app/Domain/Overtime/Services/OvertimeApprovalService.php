<?php

declare(strict_types=1);

namespace App\Domain\Overtime\Services;

use App\Domain\Overtime\Models\OvertimeForm;
use Illuminate\Support\Facades\Auth;

final class OvertimeApprovalService
{
    /**
     * Sign/approve an overtime form.
     */
    public function sign(int $formId, int $stepId): array
    {
        $form = OvertimeForm::find($formId);

        if (! $form) {
            return [
                'success' => false,
                'message' => 'Form not found',
            ];
        }

        try {
            app(\App\Application\Approval\Contracts\Approvals::class)->approve($form, auth()->id());
            
            // Update form status based on approval flow
            $this->updateFormStatus($form);
            
            return [
                'success' => true,
                'message' => 'Form signed successfully',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Reject an overtime form with a reason.
     */
    public function reject(int $formId, int $approvalId, string $description): array
    {
        $form = OvertimeForm::find($formId);

        if (! $form) {
            return [
                'success' => false,
                'message' => 'Form not found',
            ];
        }

        try {
            app(\App\Application\Approval\Contracts\Approvals::class)->reject($form, auth()->id(), $description);
            
            $form->update([
                'description' => $description,
                'status' => 'rejected',
            ]);

            return [
                'success' => true,
                'message' => 'Report rejected',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update form status based on current unified approval flow.
     */
    private function updateFormStatus(OvertimeForm $form): void
    {
        $req = $form->approvalRequest()->with('steps')->first();
        if (! $req) {
            return;
        }

        if ($req->status === 'APPROVED') {
            $form->update(['status' => 'approved']);
        } elseif ($req->status === 'IN_REVIEW') {
            $currentStep = $req->steps->where('sequence', $req->current_step)->first();
            if ($currentStep) {
                $roleSlug = $currentStep->approver_snapshot_role_slug ?? 'approver';
                $form->update(['status' => 'waiting-' . \Illuminate\Support\Str::slug($roleSlug)]);
            }
        } elseif ($req->status === 'REJECTED') {
            $form->update(['status' => 'rejected']);
        }
    }

    /**
     * Reject a detail manually from server side.
     */
    public function rejectDetail(int $detailId): array
    {
        $detail = \App\Domain\Overtime\Models\OvertimeFormDetail::find($detailId);

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

