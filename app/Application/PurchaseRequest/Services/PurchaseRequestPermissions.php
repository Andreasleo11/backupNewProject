<?php

namespace App\Application\PurchaseRequest\Services;

use App\Application\Approval\Contracts\Approvals;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\PurchaseRequest;

final class PurchaseRequestPermissions
{
    public function __construct(private readonly Approvals $approvals) {}

    public function flags(User $user, PurchaseRequest $pr): array
    {
        // --- canApprove (workflow new engine) ---
        $canApprove = false;
        if ($pr->approvalRequest) {
            $canApprove = $this->approvals->canAct($pr, (int) $user->id);
        }

        // --- canUpload (from your view rules) ---
        $canUpload =
            $user->id === $pr->user_id_create
            || $user->specification?->name === 'PURCHASER'
            || $user->is_head === 1;

        // --- canEdit (your existing complex rule, kept) ---
        $canEdit =
            ($pr->user_id_create === $user->id && $pr->status === 1)
            || ($pr->status === 1 && $user->is_head)
            || ($pr->status === 6 && $user->specification?->name === 'PURCHASER')
            || (($pr->status === 2 && $user->department?->name === 'PERSONALIA' && $user->is_head === 1)
                || ($pr->status === 7 && $user->is_gm));

        // for autograph panel
        $canAutoApprove =
            $user->is_gm
            || $user->specification?->name === 'PURCHASER'
            || $pr->from_department === 'MOULDING';

        return [
            'canApprove' => $canApprove,
            'canUpload' => $canUpload,
            'canEdit' => $canEdit,
            'canAutoApprove' => $canAutoApprove,
        ];
    }
}
