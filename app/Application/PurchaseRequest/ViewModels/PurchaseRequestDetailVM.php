<?php

namespace App\Application\PurchaseRequest\ViewModels;

use App\Models\PurchaseRequest;
use Illuminate\Support\Collection;

final class PurchaseRequestDetailVM
{
    public function __construct(
        public readonly PurchaseRequest $purchaseRequest,
        public readonly Collection $departments,
        public readonly Collection $files,
        public readonly Collection $filteredItemDetail,
        public readonly ?object $approval, // ApprovalRequest model instance
        public readonly string $fromDeptNo,
        public readonly array $totals,      // ['total' => float, 'currency' => ?string, 'hasCurrencyDiff' => bool]
        public readonly array $flags,       // ['canApprove'=>bool, 'canEdit'=>bool, 'canUpload'=>bool, ...]
        public readonly array $meta         // anything else
    ) {}
}
