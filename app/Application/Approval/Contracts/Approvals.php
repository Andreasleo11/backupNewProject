<?php

namespace App\Application\Approval\Contracts;

use App\Application\Approval\DTOs\ApprovalInfo;
use App\Domain\Approval\Contracts\Approvable;

interface Approvals
{
    /**
     * Start or submit an approval flow for a given approvable model.
     */
    public function submit(Approvable $approvable, int $byUserId, array $context = []): ApprovalInfo;

    /**
     * Approve the current step of the approvable's approval flow.
     */
    public function approve(Approvable $approvable, int $byUserId, ?string $remarks = null): void;

    /**
     * Reject the current step of the approvable's approval flow.
     */
    public function reject(Approvable $approvable, int $byUserId, ?string $remarks = null): void;

    /**
     * Get the current approval info for an approvable, if any.
     */
    public function currentRequest(Approvable $approvable): ?ApprovalInfo;

    public function canAct(Approvable $approvable, int $userId): bool;

    /**
     * Return the approvable to the creator for revision.
     */
    public function return(Approvable $approvable, int $byUserId, string $reason): void;
}
