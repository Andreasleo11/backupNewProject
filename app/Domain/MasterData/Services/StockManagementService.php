<?php

declare(strict_types=1);

namespace App\Domain\MasterData\Services;

use App\Models\Department;
use App\Models\MasterStock;
use App\Models\StockRequest;
use App\Models\StockTransaction;
use Carbon\Carbon;

final class StockManagementService
{
    /**
     * Store stock transaction (in/out).
     */
    public function storeTransaction(array $data): void
    {
        $stockId = $data['stock_id'];
        $transactionType = $data['transaction_type'];
        $itemNames = $this->extractItemNames($data);

        if ($transactionType === 'out') {
            $this->processOutTransaction($stockId, $itemNames, $data);
        } else {
            $this->processInTransaction($stockId, $itemNames);
        }
    }

    /**
     * Get available items for a master stock.
     */
    public function getAvailableItems(int $masterStockId): array
    {
        $items = StockTransaction::with('historyTransaction')
            ->where('stock_id', $masterStockId)
            ->where('is_out', false)
            ->get();

        return $items->toArray();
    }

    /**
     * Create stock request with availability calculation.
     */
    public function createStockRequest(array $data): StockRequest
    {
        $month = date('m', strtotime($data['month']));
        $masterStock = MasterStock::findOrFail($data['masterStock']);

        $sumRequested = StockRequest::where('stock_id', $data['masterStock'])
            ->whereMonth('month', $month)
            ->sum('quantity_available');

        $quantityAvailable = $this->calculateAvailableQuantity(
            $masterStock,
            $data['stockRequest'],
            $sumRequested,
            $data['masterStock'],
            $data['department']
        );

        return StockRequest::create([
            'stock_id' => $data['masterStock'],
            'dept_id' => $data['department'],
            'request_quantity' => $data['stockRequest'],
            'month' => $data['month'],
            'remark' => $data['remark'] ?? null,
            'quantity_available' => $quantityAvailable,
        ]);
    }

    /**
     * Get available quantity for stock and department.
     */
    public function getAvailableQuantity(int $stockId, int $departmentId): int
    {
        $latestTransaction = StockRequest::where('stock_id', $stockId)
            ->where('dept_id', $departmentId)
            ->orderBy('month', 'desc')
            ->latest()
            ->first();

        return $latestTransaction?->quantity_available ?? 0;
    }

    /**
     * Get filtered stock requests.
     */
    public function getFilteredStockRequests(array $filters): \Illuminate\Database\Eloquent\Collection
    {
        $query = StockRequest::with('stockRelation', 'stockRelation.stockType', 'deptRelation')
            ->orderBy('month', 'desc');

        if (! empty($filters['stock_id'])) {
            $query->where('stock_id', $filters['stock_id']);
        }

        if (! empty($filters['dept_id'])) {
            $query->where('dept_id', $filters['dept_id']);
        }

        if (! empty($filters['month'])) {
            $month = date('m', strtotime($filters['month']));
            $year = date('Y', strtotime($filters['month']));
            $query->whereMonth('month', $month)->whereYear('month', $year);
        }

        return $query->get();
    }

    /**
     * Get filtered stock transactions.
     */
    public function getFilteredTransactions(?int $stockId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = StockTransaction::with('historyTransaction', 'deptRelation');

        if ($stockId) {
            $query->where('stock_id', $stockId);
        }

        return $query->get();
    }

    /**
     * Extract item names from request data.
     */
    private function extractItemNames(array $data): array
    {
        $itemNames = [];
        foreach ($data as $key => $value) {
            if (strpos($key, 'item_name_') === 0) {
                $itemNames[] = $value;
            }
        }

        return $itemNames;
    }

    /**
     * Process out transaction.
     */
    private function processOutTransaction(int $stockId, array $itemNames, array $data): void
    {
        foreach ($itemNames as $itemName) {
            StockTransaction::where('unique_code', $itemName)
                ->where('stock_id', $stockId)
                ->update([
                    'dept_id' => $data['department'],
                    'out_time' => now(),
                    'is_out' => true,
                    'receiver' => $data['pic'],
                    'remark' => $data['remark'],
                ]);

            MasterStock::where('id', $stockId)->decrement('stock_quantity', 1);

            StockRequest::where('stock_id', $stockId)
                ->where('dept_id', $data['department'])
                ->whereYear('created_at', Carbon::now()->year)
                ->whereMonth('created_at', Carbon::now()->month)
                ->latest()
                ->decrement('quantity_available', 1);
        }
    }

    /**
     * Process in transaction.
     */
    private function processInTransaction(int $stockId, array $itemNames): void
    {
        foreach ($itemNames as $itemName) {
            StockTransaction::create([
                'stock_id' => $stockId,
                'in_time' => now(),
                'unique_code' => $itemName,
            ]);
        }

        $itemsCount = count($itemNames);
        MasterStock::where('id', $stockId)->increment('stock_quantity', $itemsCount);
    }

    /**
     * Calculate available quantity for stock request.
     */
    private function calculateAvailableQuantity(
        MasterStock $masterStock,
        int $requestQuantity,
        int $sumRequested,
        int $masterStockId,
        int $deptId
    ): int {
        $existingStockRequest = StockRequest::where('stock_id', $masterStockId)
            ->where('dept_id', $deptId)
            ->latest()
            ->first();

        if ($masterStock->stock_quantity > $requestQuantity + $sumRequested) {
            $quantityAvailable = $requestQuantity;
        } else {
            $quantityAvailable = $masterStock->stock_quantity - $sumRequested;
            if ($quantityAvailable < 0) {
                $quantityAvailable = 0;
            }
        }

        if ($existingStockRequest) {
            $quantityAvailable += $existingStockRequest->quantity_available;
        }

        return $quantityAvailable;
    }
}
