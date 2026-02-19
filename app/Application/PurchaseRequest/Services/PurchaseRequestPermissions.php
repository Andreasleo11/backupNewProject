<?php

namespace App\Application\PurchaseRequest\Services;

use App\Application\Approval\Contracts\Approvals;
use App\Application\Signature\UseCases\GetDefaultActiveUserSignature;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\PurchaseRequest;

final class PurchaseRequestPermissions
{
    public function __construct(
        private readonly Approvals $approvals,
        private readonly GetDefaultActiveUserSignature $getDefaultSignature,
    ) {}

    public function flags(User $user, PurchaseRequest $pr): array
    {
        // 1. Approve: Workflow Engine Check + Permission Check
        $canApprove = false;
        if ($pr->approvalRequest) {
            // Must support workflow logic AND have permission
            $canApprove = $this->approvals->canAct($pr, (int) $user->id)
                          && $user->can('approval.approve');
        }

        // 2. Upload: Permission Check
        $canUpload = $user->can('pr.upload-files');

        // 3. Edit: Policy Check
        // The Policy handles status checks and ownership
        $canEdit = $user->can('update', $pr);

        // 4. Auto Approve (Autograph): Role/Permission Check
        // This is a specific feature, let's map it to 'approval.approve' for now or keeps as specific check if needed
        // For now, keeping legacy logic slightly adapted or mapped to sensitive roles
        $canAutoApprove =
            $user->is_gm
            || $user->hasRole('pr-purchaser') // mapped from PURCHASER
            || $pr->from_department === 'MOULDING';

        // 5. Sign & Submit: only the creator can sign & submit their own DRAFT
        $isCreator = (int) $user->id === (int) $pr->user_id_create;
        $isDraft = $pr->workflow_status === 'DRAFT';
        $canSignAndSubmit = $isCreator && $isDraft;

        // 6. Has a saved default signature (needed to enable Sign & Submit)
        $defaultSig = $canSignAndSubmit
            ? $this->getDefaultSignature->execute((int) $user->id)
            : null;

        return [
            'canApprove' => $canApprove,
            'canUpload' => $canUpload,
            'canEdit' => $canEdit,
            'canAutoApprove' => $canAutoApprove,
            'canSignAndSubmit' => $canSignAndSubmit,
            'hasDefaultSignature' => $defaultSig !== null,
            'defaultSignaturePath' => $defaultSig?->filePath,
        ];
    }
}
