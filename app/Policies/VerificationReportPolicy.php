<?php

namespace App\Policies;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;

class VerificationReportPolicy
{
    public function view(User $u, VerificationReport $r): bool
    {
        return $u->id === $r->creator_id
            || $u->hasRole('DIRECTOR')
            || $u->can('verify-reports')
            || $this->isActiveApprover($u, $r);
    }

    public function update(User $u, VerificationReport $r): bool
    {
        return $r->status === 'DRAFT' && $u->id === $r->creator_id;
    }

    public function approve(User $u, VerificationReport $r): bool
    {
        if ($r->status !== 'IN_REVIEW') {
            return false;
        }

        return $this->isActiveApprover($u, $r);
    }

    public function reject(User $u, VerificationReport $r): bool
    {
        return $this->approve($u, $r);
    }

    /**
     * Determine if the user is the active approver for the current pending step.
     */
    private function isActiveApprover(User $u, VerificationReport $r): bool
    {
        $request = $r->approvalRequest;
        if (! $request) {
            return false;
        }

        // Use relation property instead of method to utilize eager-loaded collection cache
        $activeStep = $request->steps
            ->where('status', 'PENDING')
            ->sortBy('sequence')
            ->first();

        if (! $activeStep) {
            return false;
        }

        if ($activeStep->approver_type === 'user') {
            return (int) $u->id === (int) $activeStep->approver_id;
        }

        if ($activeStep->approver_type === 'role') {
            $slug = $activeStep->approver_snapshot_role_slug
                ?? \Spatie\Permission\Models\Role::find($activeStep->approver_id)?->name;
            return $slug && $u->hasRole($slug);
        }

        return false;
    }
}
