<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use App\Models\DetailPurchaseRequest;
use App\Models\PurchaseRequest;

final class EloquentPurchaseRequestRepository implements PurchaseRequestRepository
{
    public function create(array $header): PurchaseRequest
    {
        return PurchaseRequest::create($header);
    }

    public function addItems(PurchaseRequest $pr, array $items): void
    {
        // Batch insert for better performance (instead of N individual inserts)
        $records = array_map(function ($row) use ($pr) {
            return [
                'purchase_request_id' => $pr->id,
                'item_name' => $row['item_name'],
                'quantity' => $row['quantity'],
                'purpose' => $row['purpose'],
                'price' => $row['price'],
                'uom' => $row['uom'],
                'currency' => $row['currency'],
                'is_approve_by_head' => $row['is_approve_by_head'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $items);

        // Single INSERT with multiple rows (10-100x faster than loop)
        DetailPurchaseRequest::insert($records);
    }

    public function loadForApprovalContext(PurchaseRequest $pr): PurchaseRequest
    {
        return $pr->loadMissing([
            'items',                 // used by buildApprovalContext()
            'fromDepartment',        // used by buildApprovalContext()
            'approvalRequest',       // to check existing request
            'approvalRequest.steps', // optional, but useful
        ]);
    }

    public function getLatestByDocNumPrefix(string $prefix): ?PurchaseRequest
    {
        return PurchaseRequest::where('doc_num', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();
    }

    public function getOfficeDepartmentNames(): array
    {
        return \App\Models\Department::where('is_office', true)
            ->pluck('name')
            ->map(fn ($n) => strtoupper($n))
            ->toArray();
    }

    public function find(int $id): ?PurchaseRequest
    {
        return PurchaseRequest::find($id);
    }
}
