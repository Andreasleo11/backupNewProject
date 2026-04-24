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
     * Submit a purchase order for approval (transition to WAITING status)
     *
     * @param int $id Purchase order ID
     *
     * @throws \Exception
     */
    public function submitForApproval(int $id): PurchaseOrder
    {
        try {
            return DB::transaction(function () use ($id) {
                $po = PurchaseOrder::findOrFail($id);

                if (! $po->canTransitionTo(PurchaseOrderStatus::WAITING)) {
                    throw new \InvalidArgumentException('Purchase order cannot be submitted for approval');
                }

                $po->setStatusEnum(PurchaseOrderStatus::WAITING);
                $po->save();

                // TODO: Trigger approval workflow
                // TODO: Send notifications

                Log::info('Purchase order submitted for approval', [
                    'po_id' => $po->id,
                    'po_number' => $po->po_number,
                    'submitted_by' => auth()->id(),
                ]);

                return $po;
            });
        } catch (\Exception $e) {
            Log::error('Failed to submit purchase order for approval', [
                'po_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
