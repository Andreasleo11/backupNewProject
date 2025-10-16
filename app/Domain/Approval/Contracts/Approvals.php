<?php

namespace App\Domain\Approval\Contracts;

use App\Infrastructure\Approval\Models\ApprovalRequest;

interface Approvals
{
    public function submit(object $approvable, int $byUserId, array $context = []): ApprovalRequest;

    public function approve(object $approvable, int $byUserId, ?string $remarks = null): void;

    public function reject(object $approvable, int $byUserId, ?string $remarks = null): void;

    public function currentRequest(object $approvable): ?ApprovalRequest;
}
