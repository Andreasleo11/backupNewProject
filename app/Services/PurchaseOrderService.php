<?php

namespace App\Services;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderService
{
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
                // Create the purchase order
                $purchaseOrder = new PurchaseOrder;
                $purchaseOrder->po_number = $data['po_number'];
                $purchaseOrder->status = PurchaseOrderStatus::DRAFT->legacyValue(); // Start as draft
                $purchaseOrder->filename = $data['pdf_file'] ?? null;
                $purchaseOrder->creator_id = auth()->id();
                $purchaseOrder->vendor_name = $data['vendor_name'];
                $purchaseOrder->invoice_date = $data['invoice_date'];
                $purchaseOrder->invoice_number = $data['invoice_number'];
                $purchaseOrder->currency = $data['currency'];
                $purchaseOrder->total = $data['total'];
                $purchaseOrder->purchase_order_category_id = $data['purchase_order_category_id'];
                $purchaseOrder->tanggal_pembayaran = $data['tanggal_pembayaran'];

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

                Log::info('Purchase order created', [
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
                $po->invoice_date = $data['invoice_date'];
                $po->invoice_number = $data['invoice_number'];
                $po->tanggal_pembayaran = $data['tanggal_pembayaran'];
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
            DB::transaction(function () use ($id, $userId, $remarks) {
                $po = PurchaseOrder::findOrFail($id);

                if (! $po->canTransitionTo(PurchaseOrderStatus::APPROVED)) {
                    throw new \InvalidArgumentException('Purchase order cannot be approved');
                }

                $po->setStatusEnum(PurchaseOrderStatus::APPROVED);
                $po->approved_date = now();
                $po->save();

                Log::info('Purchase order approved', [
                    'po_id' => $po->id,
                    'po_number' => $po->po_number,
                    'approved_by' => $userId,
                    'remarks' => $remarks,
                ]);
            });
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
                ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$selectedMonth])
                ->groupBy('vendor_name')
                ->orderByDesc('total')
                ->get();

            // Fetch top 5 vendors
            $topVendors = PurchaseOrder::selectRaw('vendor_name')
                ->selectRaw('SUM(total) as total')
                ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$selectedMonth])
                ->groupBy('vendor_name')
                ->orderByDesc('total')
                ->take(5)
                ->get();

            // Sum of totals for each month (for chart)
            $monthlyTotals = PurchaseOrder::selectRaw(
                "DATE_FORMAT(invoice_date, '%Y-%m') as month, SUM(total) as total",
            )
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // List of available months for the filter dropdown
            $availableMonths = PurchaseOrder::selectRaw("DATE_FORMAT(invoice_date, '%Y-%m') as month")
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
                ->select('id', 'po_number', 'invoice_date', 'total', 'status')
                ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$month])
                ->orderBy('invoice_date', 'desc')
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
