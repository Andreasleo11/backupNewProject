<?php

namespace App\Application\PurchaseRequest\DTOs;

final class ApprovalActionDTO
{
    public function __construct(
        public readonly int $purchaseRequestId,
        public readonly int $actorUserId,
        public readonly ?string $remarks,
        /**
         * When true (default), any pending item-level approvals for the current
         * workflow step are automatically resolved before the step is advanced.
         * Set explicitly to true from the Quick-View approve action in the index
         * datatable, and kept true by default so existing call-sites are unaffected.
         */
        public readonly bool $autoApproveItems = true,
    ) {}
}
