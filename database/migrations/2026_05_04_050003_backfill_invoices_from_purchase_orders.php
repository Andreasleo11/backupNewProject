<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ONE-TIME DATA MIGRATION
 *
 * Goal: For every group of purchase_orders sharing the same po_number,
 *       elect the earliest-created record as the "parent" PO, insert one
 *       Invoice row per record in the group, then soft-delete all non-parent
 *       duplicates so the po_number column can be made unique going forward.
 *
 * Safety:
 *   - Wrapped in a DB transaction — rolls back entirely on any error.
 *   - Idempotent guard: skips if invoices table already has data.
 *   - Existing invoice columns on purchase_orders are NOT touched.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Idempotency guard — safe to re-run after a failed deploy
        if (DB::table('invoices')->exists()) {
            Log::info('[backfill_invoices] invoices table already has rows — skipping.');

            return;
        }

        DB::transaction(function () {
            // 1. Find all po_numbers that have at least one record (including singletons)
            $groups = DB::table('purchase_orders')
                ->select('po_number')
                ->whereNull('deleted_at')
                ->groupBy('po_number')
                ->pluck('po_number');

            $totalInvoices = 0;
            $totalSoftDeleted = 0;

            foreach ($groups as $poNumber) {
                // 2. Fetch all POs for this po_number, oldest first
                $records = DB::table('purchase_orders')
                    ->where('po_number', $poNumber)
                    ->whereNull('deleted_at')
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->get();

                if ($records->isEmpty()) {
                    continue;
                }

                // 3. The first record becomes the canonical / parent PO
                $parentId = $records->first()->id;

                // 4. Insert one Invoice row for EVERY record in this group
                //    (the parent PO's own invoice data is also captured)
                $invoiceRows = $records->map(fn ($po) => [
                    'purchase_order_id' => $parentId,
                    'invoice_number' => $po->invoice_number ?? null,
                    'invoice_date' => $po->invoice_date ?? null,
                    'payment_date' => $po->tanggal_pembayaran ?? null,
                    'total' => $po->total ?? null,
                    'total_currency' => $po->currency ?? null,
                    'created_at' => $po->created_at,
                    'updated_at' => now(),
                ])->toArray();

                // Use insertOrIgnore to skip duplicate (po_id, invoice_number) silently
                DB::table('invoices')->insertOrIgnore($invoiceRows);
                $totalInvoices += count($invoiceRows);

                // 5. Soft-delete all non-parent duplicates
                $duplicateIds = $records
                    ->where('id', '!=', $parentId)
                    ->pluck('id')
                    ->toArray();

                if (! empty($duplicateIds)) {
                    DB::table('purchase_orders')
                        ->whereIn('id', $duplicateIds)
                        ->update(['deleted_at' => now()]);

                    $totalSoftDeleted += count($duplicateIds);
                }
            }

            Log::info(sprintf(
                '[backfill_invoices] Done. %d invoice rows inserted, %d duplicate POs soft-deleted.',
                $totalInvoices,
                $totalSoftDeleted
            ));
        });
    }

    /**
     * Reverse: restore soft-deleted PO duplicates and drop all inserted invoices.
     * NOTE: This only restores structural data — file attachments remain on the parent PO.
     */
    public function down(): void
    {
        DB::transaction(function () {
            // Remove all backfilled invoices
            DB::table('invoices')->delete();

            // Restore soft-deleted POs
            DB::table('purchase_orders')
                ->whereNotNull('deleted_at')
                ->update(['deleted_at' => null]);
        });
    }
};
