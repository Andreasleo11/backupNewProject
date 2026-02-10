<?php

declare(strict_types=1);

namespace App\Domain\PurchaseRequest\Services;

use App\Domain\PurchaseRequest\ValueObjects\WorkflowStep;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\DetailPurchaseRequest;
use App\Models\PurchaseRequest;

/**
 * Domain Service for item-level authorization.
 * Encapsulates business rules about who can approve/reject items.
 */
final class ItemApprovalAuthorizationService
{
    /**
     * Check if a user can approve or reject an item.
     */
    public function canApproveOrReject(User $user, DetailPurchaseRequest $item): bool
    {
        $pr = $item->purchaseRequest;

        if (! $pr) {
            return false;
        }

        if ($pr->is_cancel) {
            return false;
        }

        $workflowStep = $this->getCurrentWorkflowStep($pr);

        if (! $workflowStep || ! $workflowStep->requiresItemApproval()) {
            return false;
        }

        // Check user authorization based on workflow step
        return match ($workflowStep->approverType()) {
            'head' => $this->canActAsDeptHead($user, $pr),
            'verificator' => $this->canActAsVerificator($user),
            'director' => $this->canActAsDirector($user),
            default => false,
        };
    }

    /**
     * Get the workflow step for a purchase request.
     */
    public function getCurrentWorkflowStep(PurchaseRequest $pr): ?WorkflowStep
    {
        $approval = $pr->approvalRequest;
        if (! $approval) {
            return null;
        }

        $currentStepData = $approval->steps()->where('sequence', $approval->current_step)->first();
        if (! $currentStepData) {
            return null;
        }

        $roleSlug = null;
        if ($currentStepData->approver_type === 'role') {
            $role = \Spatie\Permission\Models\Role::find($currentStepData->approver_id);
            $roleSlug = $role?->name;
        } else {
            // If we have other approver types (strategies), we might need logic here
            // For now, only role-based steps support item approval
            return null;
        }

        if (! $roleSlug) {
            return null;
        }

        return WorkflowStep::fromRoleSlug($roleSlug);
    }

    /**
     * Business rule: Can user act as department head?
     */
    private function canActAsDeptHead(User $user, PurchaseRequest $pr): bool
    {
        // Must be a department head
        if (! $user->hasRole('HEAD')) {
            return false;
        }

        // Special case: PERSONALIA department requires matching department
        if ($pr->from_department === 'PERSONALIA') {
            return $user->department?->name === 'PERSONALIA';
        }

        // Special case: STORE department allows any head
        if ($pr->from_department === 'STORE') {
            // But still check Moulding restrictions if applicable? No, separate concern.
            return true;
        }

        // Default: must be head
        // Also check Moulding specific restriction
        if ($pr->from_department === 'MOULDING') {
             if (! $this->passesMouldingRestrictions($user, $pr)) {
                 return false;
             }
        }

        if($pr->from_department === $user->department?->name){
            return true;
        }

        return false;
    }

    /**
    * Check special restrictions for Moulding department.
    * Logic: If Import, Designers cannot approve. If Local, anyone can (effectively).
    */
    private function passesMouldingRestrictions(User $user, PurchaseRequest $pr): bool 
    {
        // Replicating Blade Logic:
        // ($purchaseRequest->is_import === 1 && ( !auth()->user()->hasRole('DESIGN'))) ||
        // (!$purchaseRequest->is_import && ( !auth()->user()->hasRole('DESIGN'))) ||
        // ($purchaseRequest->is_import === 0 && (auth()->user()->hasRole('DESIGN'))) ||
        // (!$purchaseRequest->is_import && (auth()->user()->hasRole('DESIGN')));
        
        $isImport = (int) $pr->is_import === 1;
        $isDesigner = $user->hasRole('DESIGN');

        if ($isImport) {
            // If import, user MUST NOT be designer
            return ! $isDesigner;
        }

        // If not import, ANYONE allowed based on the messy blade logic 
        // (true && !designer) || (true && designer) => true
        return true;
    }

    /**
     * Business rule: Can user act as verificator?
     */
    private function canActAsVerificator(User $user): bool
    {
        return $user->hasRole('VERIFICATOR');
    }

    /**
     * Business rule: Can user act as director?
     */
    private function canActAsDirector(User $user): bool
    {
        return $user->hasRole('DIRECTOR');
    }
}
