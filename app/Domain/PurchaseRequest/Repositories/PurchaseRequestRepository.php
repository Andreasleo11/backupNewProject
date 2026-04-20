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

    public function getLatestByDocNumPrefix(string $prefix): ?PurchaseRequest;

    /** @return string[] */
    public function getOfficeDepartmentNames(): array;

    public function find(int $id): ?PurchaseRequest;

    /**
     * Soft delete a purchase request (cascades to items).
     */
    public function delete(PurchaseRequest $pr): bool;

    /**
     * Restore a soft-deleted purchase request (cascades to items).
     */
    public function restore(PurchaseRequest $pr): bool;

    /**
     * Permanently delete a purchase request (cascades to items).
     */
    public function forceDelete(PurchaseRequest $pr): bool;

    /**
     * Update only the PO number on an approved Purchase Request
     */
    public function updatePoNumber(PurchaseRequest $pr, ?string $poNumber): bool;
}
