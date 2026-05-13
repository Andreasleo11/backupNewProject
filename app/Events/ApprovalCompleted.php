<?php

namespace App\Events;

use App\Domain\Approval\Contracts\Approvable;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use Illuminate\Foundation\Events\Dispatchable;

class ApprovalCompleted
{
    use Dispatchable;

    public function __construct(
        public Approvable $approvable,
        public ApprovalRequest $approvalRequest
    ) {}
}