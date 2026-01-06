<?php

namespace App\Application\PurchaseRequest\DTOs;

final class ApprovalActionDTO
{
    public function __construct(
        public readonly int $purchaseRequestId,
        public readonly int $actorUserId,
        public readonly ?string $remarks,
    ) {}
}
