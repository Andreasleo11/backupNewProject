<?php

namespace App\Infrastructure\Approval\Concerns;

use App\Infrastructure\Approval\Models\ApprovalRequest;

trait HasApproval
{
    public function approvalRequest()
    {
        return $this->morphOne(ApprovalRequest::class, 'approvable');
    }

    // convenience
    public function approvalStatus(): string
    {
        return optional($this->approvalRequest)->status ?? 'DRAFT';
    }
}
