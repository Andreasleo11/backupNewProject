<?php

namespace App\Services;

use App\Application\Approval\Contracts\Approvals;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderService
{
    public function __construct(private Approvals $approvals) {}

    /**
     * Create a new purchase order
     *
     * @param array $data Validated form data
     *
     * @throws \Exception
     */
    public function create(array $data): PurchaseOrder
    {
        try {
            return DB::transaction(function () use ($data) {
                // Create the purchase order and submit for director approval
                $purchaseOrder = new PurchaseOrder;
                $purchaseOrder->po_number = $data['po_number'];
                $purchaseOrder->filename = $data['pdf_file'] ?? null;
                $purchaseOrder->creator_id = auth()->id();
                $purchaseOrder->vendor_name = $data['vendor_name'];
                $purchaseOrder->currency = $data['currency'];
                $purchaseOrder->total = $data['total'];
                $purchaseOrder->purchase_order_category_id = $data['purchase_order_category_id'];

                // Handle parent PO revision logic
                if (! empty($data['parent_po_number'])) {
                    $purchaseOrder->parent_po_number = $data['parent_po_number'];

                    // Update the canceled PO revision_count
                    $parentPO = PurchaseOrder::where('po_number', $data['parent_po_number'])->first();
                    if ($parentPO) {
                        $parentPO->update([
                            'revision_count' => $parentPO->revision_count + 1,
                        ]);
                    }
                }

                $purchaseOrder->save();

                // Submit for approval - this creates approval request (status remains PENDING_APPROVAL)
                $this->approvals->submit($purchaseOrder, auth()->id());

                Log::info('Purchase order created and submitted for approval', [
                    'po_number' => $purchaseOrder->po_number,
                    'creator_id' => $purchaseOrder->creator_id,
                    'total' => $purchaseOrder->total,
                ]);

                return $purchaseOrder;
            });
        } catch (\Exception $e) {
            Log::error('Failed to create purchase order', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing purchase order
     *
     * @param int $id Purchase order ID
     * @param array $data Validated form data
     *
     * @throws \Exception
     */
    public function update(int $id, array $data): PurchaseOrder
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $po = PurchaseOrder::findOrFail($id);

                // Validate that PO can be edited
                if (! $po->getStatusEnum()->canEdit()) {
                    throw new \InvalidArgumentException('Purchase order cannot be edited in its current status');
                }

                // Update fields
                $po->po_number = $data['po_number'];
                $po->vendor_name = $data['vendor_name'];
                $po->currency = $data['currency'];
                $po->purchase_order_category_id = $data['purchase_order_category_id'];
                $po->total = $data['total']; // Note: commas should be removed by validation

                // Handle PDF file update
                if (isset($data['pdf_file'])) {
                    // Delete old file if exists
                    if ($po->filename) {
                        // TODO: Implement file deletion via PdfProcessingService
                    }
                    $po->filename = $data['pdf_file'];
                }

                $po->save();

                Log::info('Purchase order updated', [
                    'po_id' => $po->id,
                    'po_number' => $po->po_number,
                    'updated_by' => auth()->id(),
                ]);

                return $po;
            });
        } catch (\Exception $e) {
            Log::error('Failed to update purchase order', [
                'po_id' => $id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete a purchase order
     *
     * @param int $id Purchase order ID
     *
     * @throws \Exception
     */
    public function delete(int $id): bool
    {
        try {
            $po = PurchaseOrder::findOrFail($id);

            // TODO: Add business rules for deletion (only draft status, etc.)

            $po->delete();

            Log::info('Purchase order deleted', [
                'po_id' => $id,
                'po_number' => $po->po_number,
                'deleted_by' => auth()->id(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete purchase order', [
                'po_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Approve a purchase order
     *
     * @param int $id Purchase order ID
     * @param int $userId User performing the approval
     * @param string|null $remarks Approval remarks
     *
     * @throws \Exception
     */
    public function approve(int $id, int $userId, ?string $remarks = null): void
    {
        try {
            $po = PurchaseOrder::findOrFail($id);

            // Update approval date
            $po->approved_date = now();
            $po->save();

            // Use approval engine to approve - this will update status automatically
            $this->approvals->approve($po, $userId, $remarks);

            Log::info('Purchase order approval processed via approval engine', [
                'po_id' => $po->id,
                'po_number' => $po->po_number,
                'approved_by' => $userId,
                'remarks' => $remarks,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to approve purchase order', [
                'po_id' => $id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reject a purchase order using the unified approval system
     *
     * @param int $id Purchase order ID
     * @param int $userId User rejecting the PO
     * @param string $reason Rejection reason
     *
     * @throws \Exception
     */
    /**
     * Approve multiple purchase orders
     *
     * @param array $ids Array of PO IDs
     * @param int $userId User performing the approval
     *
     * @throws \Exception
     */
    public function approveAll(array $ids, int $userId): void
    {
        try {
            DB::transaction(function () use ($ids, $userId) {
                foreach ($ids as $id) {
                    $this->approve($id, $userId);
                }
            });
        } catch (\Exception $e) {
            Log::error('Failed to approve multiple purchase orders', [
                'ids' => $ids,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reject a purchase order
     *
     * @param int $id Purchase order ID
     * @param int $userId User performing the rejection
     * @param string $reason Rejection reason
     *
     * @throws \Exception
     */
    public function reject(int $id, int $userId, string $reason): void
    {
        try {
            $po = PurchaseOrder::findOrFail($id);

            // Use approval engine to reject
            $this->approvals->reject($po, $userId, $reason);

            Log::info('Purchase order rejection processed via approval engine', [
                'po_id' => $po->id,
                'po_number' => $po->po_number,
                'rejected_by' => $userId,
                'reason' => $reason,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to reject purchase order', [
                'po_id' => $id,
                'user_id' => $userId,
                'reason' => $reason,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reject multiple purchase orders
     *
     * @param array $ids Array of PO IDs
     * @param int $userId User performing the rejection
     * @param string $reason Rejection reason
     *
     * @throws \Exception
     */
    public function rejectAll(array $ids, int $userId, string $reason): void
    {
        try {
            DB::transaction(function () use ($ids, $userId, $reason) {
                foreach ($ids as $id) {
                    $this->reject($id, $userId, $reason);
                }
            });
        } catch (\Exception $e) {
            Log::error('Failed to reject multiple purchase orders', [
                'ids' => $ids,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancel a purchase order
     *
     * @param int $id Purchase order ID
     * @param string $reason Cancellation reason
     *
     * @throws \Exception
     */
    public function cancel(int $id, string $reason): void
    {
        try {
            $po = PurchaseOrder::findOrFail($id);

            // Use approval engine to cancel
            $this->approvals->cancel($po, auth()->id(), $reason);

            Log::info('Purchase order cancelled via approval engine', [
                'po_id' => $po->id,
                'po_number' => $po->po_number,
                'cancelled_by' => auth()->id(),
                'reason' => $reason,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to cancel purchase order', [
                'po_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get dashboard analytics data
     *
     * @param string $month Month in Y-m format
     */
    public function getDashboardData(?string $month = null): array
    {
        try {
            $selectedMonth = $month ?: now()->format('Y-m');

            // Query for vendor totals (distinct vendors with their totals)
            $vendorTotals = PurchaseOrder::selectRaw(
                'vendor_name, COUNT(id) as po_count, SUM(total) as total',
            )
                ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$selectedMonth])
                ->groupBy('vendor_name')
                ->orderByDesc('total')
                ->get();

            // Fetch top 5 vendors
            $topVendors = PurchaseOrder::selectRaw('vendor_name')
                ->selectRaw('SUM(total) as total')
                ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$selectedMonth])
                ->groupBy('vendor_name')
                ->orderByDesc('total')
                ->take(5)
                ->get();

            // Sum of totals for each month (for chart)
            $monthlyTotals = PurchaseOrder::selectRaw(
                "DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as total",
            )
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // List of available months for the filter dropdown
            $availableMonths = PurchaseOrder::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month")
                ->distinct()
                ->orderByDesc('month')
                ->pluck('month');

            // Fetch counts for approved, waiting, and rejected using query scopes
            $statusCounts = [
                'approved' => PurchaseOrder::approved()->count(),
                'waiting' => PurchaseOrder::waiting()->count(),
                'rejected' => PurchaseOrder::rejected()->count(),
                'canceled' => PurchaseOrder::canceled()->count(),
            ];

            // Fetch Purchase Order counts grouped by category
            $poByCategory = PurchaseOrder::selectRaw('purchase_order_category_id, COUNT(*) as count')
                ->groupBy('purchase_order_category_id')
                ->get();

            // Fetch category names for better readability
            $categories = \App\Models\PurchaseOrderCategory::whereIn(
                'id',
                $poByCategory->pluck('purchase_order_category_id'),
            )->pluck('name', 'id'); // Returns [id => name]

            // Format data for chart
            $categoryChartData = $poByCategory->map(function ($po) use ($categories) {
                return [
                    'label' => $categories[$po->purchase_order_category_id] ?? 'Unknown',
                    'count' => $po->count,
                ];
            });

            return [
                'monthlyTotals' => $monthlyTotals,
                'topVendors' => $topVendors,
                'vendorTotals' => $vendorTotals,
                'availableMonths' => $availableMonths,
                'selectedMonth' => $selectedMonth,
                'statusCounts' => $statusCounts,
                'categoryChartData' => $categoryChartData,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get dashboard data', [
                'month' => $month,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get vendor details for a specific month
     *
     * @param string $month Month in Y-m format
     */
    public function getVendorDetails(string $vendorName, string $month): \Illuminate\Support\Collection
    {
        try {
            return PurchaseOrder::where('vendor_name', $vendorName)
                ->select('id', 'po_number', 'created_at', 'total', 'status')
                ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
                ->orderBy('created_at', 'desc')
                ->orderByDesc('total')
                ->get();

        } catch (\Exception $e) {
            Log::error('Failed to get vendor details', [
                'vendor' => $vendorName,
                'month' => $month,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
