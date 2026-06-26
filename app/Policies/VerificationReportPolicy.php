<?php

namespace App\Policies;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;

class VerificationReportPolicy
{
    public function view(User $u, VerificationReport $r): bool
    {
        return $u->id === $r->creator_id || $u->hasRole('DIRECTOR') || $u->can('verify-reports');
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

        $request = $r->approvalRequest;
        if (! $request) {
            return false;
        }

        $activeStep = $request->steps()
            ->where('status', 'PENDING')
            ->orderBy('sequence')
            ->first();

        if (! $activeStep) {
            return false;
        }

        if ($activeStep->approver_type === 'user') {
            return (int) $u->id === (int) $activeStep->approver_id;
        }

        if ($activeStep->approver_type === 'role') {
            return $u->hasRole((int) $activeStep->approver_id);
        }

        return false;
    }

    public function reject(User $u, VerificationReport $r): bool
    {
        return $this->approve($u, $r);
    }
}
