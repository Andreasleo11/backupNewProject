<?php

declare(strict_types=1);

namespace App\Domain\PurchaseRequest\Repositories;

use App\Models\PurchaseRequest;

interface PurchaseRequestRepository
{
    public function create(array $header): PurchaseRequest;

    /** @param list<array<string,mixed>> $items */
    public function addItems(PurchaseRequest $pr, array $items): void;

    public function loadForApprovalContext(PurchaseRequest $pr): PurchaseRequest;
}
