<?php

namespace App\Policies;

use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use App\Models\User;

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
        return $r->status === 'IN_REVIEW';
    }
}
