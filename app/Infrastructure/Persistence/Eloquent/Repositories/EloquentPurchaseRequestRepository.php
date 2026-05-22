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
        // Wrap in a transaction to maintain high execution speed
        // while using Eloquent to trigger the Spatie Activitylog events
        \Illuminate\Support\Facades\DB::transaction(function () use ($pr, $items) {
            foreach ($items as $row) {
                $pr->items()->create([
                    'item_name' => $row['item_name'],
                    'quantity' => $row['quantity'],
                    'purpose' => $row['purpose'],
                    'price' => $row['price'],
                    'uom' => $row['uom'],
                    'currency' => $row['currency'],
                    'is_approve_by_head' => $row['is_approve_by_head'] ?? null,
                ]);
            }
        });
    }

    public function loadForApprovalContext(PurchaseRequest $pr): PurchaseRequest
    {
        return $pr->load([
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
        return \App\Infrastructure\Persistence\Eloquent\Models\Department::where('is_office', true)
            ->pluck('name')
            ->map(fn ($n) => strtoupper($n))
            ->toArray();
    }

    public function find(int $id): ?PurchaseRequest
    {
        return PurchaseRequest::find($id);
    }

    /**
     * Soft delete a purchase request and cascade to its items.
     * This is infrastructure-level cascade logic.
     */
    public function delete(PurchaseRequest $pr): bool
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($pr) {
            // Load and iterate to trigger deleting events for each item
            $pr->items->each->delete();

            // Then soft delete the purchase request
            return $pr->delete();
        });
    }

    /**
     * Restore a soft-deleted purchase request and its items.
     */
    public function restore(PurchaseRequest $pr): bool
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($pr) {
            // Restore the purchase request
            $restored = $pr->restore();

            // Cascade restore to items, iterating to trigger events
            if ($restored) {
                $pr->items()->withTrashed()->get()->each->restore();
            }

            return $restored;
        });
    }

    /**
     * Permanently delete a purchase request and its items.
     */
    public function forceDelete(PurchaseRequest $pr): bool
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($pr) {
            // Force delete items first, iterating to trigger events
            $pr->items()->withTrashed()->get()->each->forceDelete();

            // Then force delete the purchase request
            return $pr->forceDelete();
        });
    }

    public function updatePoNumber(PurchaseRequest $pr, ?string $poNumber): bool
    {
        return $pr->update([
            'po_number' => $poNumber,
        ]);
    }
}
