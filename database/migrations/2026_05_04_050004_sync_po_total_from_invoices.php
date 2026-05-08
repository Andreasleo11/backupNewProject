<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ONE-TIME SYNC MIGRATION
 *
 * Recalculates purchase_orders.total for every active PO so that it equals
 * the SUM of its related invoices.total (non-soft-deleted invoices only).
 *
 * Run after: 2026_05_04_050003_backfill_invoices_from_purchase_orders
 *
 * Safe to re-run — it is a pure UPDATE, no rows are created or deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Single query: UPDATE purchase_orders SET total = invoice SUM
        // Uses a correlated sub-query for compatibility with MySQL 5.7+
        $affected = DB::statement('
            UPDATE purchase_orders po
            SET po.total = (
                SELECT COALESCE(SUM(i.total), 0)
                FROM invoices i
                WHERE i.purchase_order_id = po.id
                  AND i.deleted_at IS NULL
            )
            WHERE po.deleted_at IS NULL
        ');

        // Count how many rows were actually touched for logging
        $count = DB::table('purchase_orders')
            ->whereNull('deleted_at')
            ->count();

        Log::info("[sync_po_total_from_invoices] Updated total on {$count} active purchase orders.");
    }

    /**
     * No reliable down() for this migration — the original per-row totals were
     * already stored correctly on the backfilled invoices, so "reversing" would
     * mean re-running the backfill migration's data, which is not safe to do here.
     *
     * To revert: restore purchase_orders from a pre-migration backup.
     */
    public function down(): void
    {
        Log::warning('[sync_po_total_from_invoices] down() is a no-op — restore from backup if needed.');
    }
};
