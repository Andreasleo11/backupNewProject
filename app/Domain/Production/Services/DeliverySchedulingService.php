<?php

declare(strict_types=1);

namespace App\Domain\Production\Services;

use App\Models\delsched_delfilter;
use App\Models\delsched_delsum;
use App\Models\delsched_final;
use App\Models\delsched_solist;
use App\Models\delsched_stock;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;

final class DeliverySchedulingService
{
    /**
     * Process delivery schedule - Step 1: Initial data setup.
     */
    public function processInitialSetup(): void
    {
        // Truncate working tables
        DB::table('delsched_final')->truncate();
        DB::table('delsched_solist')->truncate();
        DB::table('delsched_delfilter')->truncate();
        DB::table('delsched_delsum')->truncate();
        DB::table('delsched_stock')->truncate();
        DB::table('delsched_finalwip')->truncate();
        DB::table('delsched_stockwip')->truncate();

        // Pull SAP delivery schedule data
        $this->pullSapDelSchedToFinal();

        // Pull SAP delivery sales orders
        $this->pullSapDelSoToSoList();

        // Pull SAP actual delivery data
        $this->pullSapDelActualToDelFilter();

        // Create delivery summary
        $this->createDeliverySummary();
    }

    /**
     * Process delivery schedule - Step 2: Filter by closed SO.
     */
    public function filterByClosedSalesOrders(): void
    {
        $deliveries = DB::table('delsched_final')
            ->where('so_number', '<>', '')
            ->get();

        foreach ($deliveries as $delivery) {
            $soList = DB::table('delsched_solist')
                ->where('so_number', $delivery->so_number)
                ->first();

            if ($soList->so_status == 'C') {
                DB::table('delsched_final')
                    ->where('id', $delivery->id)
                    ->update([
                        'delivered' => $delivery->delivery_qty,
                        'outstanding' => 0,
                        'outstanding_stk' => 0,
                        'doc_status' => 'C',
                        'status' => 'success',
                    ]);
            }
        }
    }

    /**
     * Process delivery schedule - Step 3: Calculate outstanding with delivery reduction.
     */
    public function calculateOutstandingWithDeliveries(): void
    {
        $deliveries = DB::table('delsched_final')
            ->where('doc_status', '=', 'O')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($deliveries as $delivery) {
            $delSum = DB::table('delsched_delsum')
                ->where('item_code', $delivery->item_code)
                ->first();

            $totalAfter = $delSum->total_after ?? 0;

            if ($totalAfter <= 0) {
                $this->updateDeliveryDanger($delivery->id, $delivery->delivery_qty);
            } elseif ($totalAfter >= $delivery->delivery_qty) {
                $this->updateDeliverySuccess($delivery, $delSum, $totalAfter);
            } else {
                $this->updateDeliveryWarning($delivery, $delSum, $totalAfter);
            }
        }
    }

    /**
     * Process delivery schedule - Step 4: Apply stock balancing.
     */
    public function applyStockBalancing(): void
    {
        // Create stock records for each item
        $items = DB::table('delsched_final')
            ->select('item_code')
            ->distinct()
            ->get();

        foreach ($items as $item) {
            $inventory = DB::table('sap_inventory_fg')
                ->where('item_code', '=', $item->item_code)
                ->first();

            delsched_stock::insert([
                'item_code' => $item->item_code,
                'quantity' => $inventory->stock,
                'total_after' => $inventory->stock,
            ]);
        }

        // Apply stock to deliveries
        $deliveries = DB::table('delsched_final')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($deliveries as $delivery) {
            $this->applyStockToDelivery($delivery);
        }

        // Update last update timestamp
        $this->updateLastProcessTime(13);
    }

    /**
     * Pull SAP delivery schedule to final table.
     */
    private function pullSapDelSchedToFinal(): void
    {
        $sapDeliveries = DB::table('sap_delsched')
            ->orderBy('delivery_date', 'asc')
            ->orderBy('item_code', 'asc')
            ->get();

        foreach ($sapDeliveries as $sap) {
            $inventory = DB::table('sap_inventory_fg')
                ->where('item_code', $sap->item_code)
                ->first();

            $customer = DB::table('sap_fg_customer')
                ->where('item_code', $sap->item_code)
                ->first();

            $departement = match ($inventory->process_owner) {
                'INJ' => 390,
                'SEC' => 361,
                default => 362,
            };

            delsched_final::insert([
                'delivery_date' => $sap->delivery_date,
                'item_code' => $sap->item_code,
                'item_name' => $inventory->item_name,
                'delivery_qty' => $sap->delivery_qty,
                'so_number' => $sap->so_number,
                'doc_status' => 'O',
                'packaging_code' => $inventory->packaging,
                'standar_pack' => $inventory->standar_packing,
                'customer_code' => $customer->customer_code ?? '',
                'customer_name' => $customer->customer_name ?? '',
                'departement' => $departement,
            ]);
        }
    }

    /**
     * Pull SAP delivery SO to solist table.
     */
    private function pullSapDelSoToSoList(): void
    {
        $sapSalesOrders = DB::table('sap_delso')
            ->orderBy('doc_num', 'asc')
            ->orderBy('item_no', 'asc')
            ->get();

        foreach ($sapSalesOrders as $so) {
            delsched_solist::insert([
                'so_number' => $so->doc_num,
                'so_status' => $so->doc_status,
                'item_code' => $so->item_no,
                'so_qty' => $so->quantity,
                'delivered_qty' => $so->delivered_qty,
                'row_status' => $so->row_status,
            ]);
        }
    }

    /**
     * Pull SAP actual delivery to delfilter (only open SO).
     */
    private function pullSapDelActualToDelFilter(): void
    {
        $sapActuals = DB::table('sap_delactual')->get();

        foreach ($sapActuals as $actual) {
            $soList = DB::table('delsched_solist')
                ->where('so_number', $actual->so_num)
                ->first();

            $status = ($soList->so_status ?? 'O') == 'O' ? 'O' : 'C';

            if ($status == 'O') {
                delsched_delfilter::insert([
                    'item_code' => $actual->item_no,
                    'delivery_date' => $actual->delivery_date,
                    'quantity' => $actual->quantity,
                    'so_number' => $actual->so_num,
                ]);
            }
        }
    }

    /**
     * Create delivery summary per item.
     */
    private function createDeliverySummary(): void
    {
        $items = DB::table('delsched_delfilter')
            ->select('item_code')
            ->distinct()
            ->get();

        foreach ($items as $item) {
            $totalQty = DB::table('delsched_delfilter')
                ->where('item_code', $item->item_code)
                ->sum('quantity');

            delsched_delsum::insert([
                'item_code' => $item->item_code,
                'quantity' => $totalQty,
                'total_after' => $totalQty,
            ]);
        }
    }

    /**
     * Update delivery with danger status.
     */
    private function updateDeliveryDanger(int $deliveryId, float $qty): void
    {
        DB::table('delsched_final')
            ->where('id', $deliveryId)
            ->update([
                'delivered' => 0,
                'outstanding' => $qty,
                'outstanding_stk' => $qty,
                'status' => 'danger',
            ]);
    }

    /**
     * Update delivery with success status.
     */
    private function updateDeliverySuccess($delivery, $delSum, float $totalAfter): void
    {
        $newTotal = $totalAfter - $delivery->delivery_qty;

        DB::table('delsched_delsum')
            ->where('id', $delSum->id)
            ->update(['total_after' => $newTotal]);

        DB::table('delsched_final')
            ->where('id', $delivery->id)
            ->update([
                'delivered' => $delivery->delivery_qty,
                'outstanding' => 0,
                'outstanding_stk' => 0,
                'status' => 'success',
            ]);
    }

    /**
     * Update delivery with warning status.
     */
    private function updateDeliveryWarning($delivery, $delSum, float $totalAfter): void
    {
        $outstanding = $delivery->delivery_qty - $totalAfter;

        DB::table('delsched_delsum')
            ->where('id', $delSum->id)
            ->update(['total_after' => 0]);

        DB::table('delsched_final')
            ->where('id', $delivery->id)
            ->update([
                'delivered' => $totalAfter,
                'outstanding' => $outstanding,
                'outstanding_stk' => $outstanding,
                'status' => 'warning',
            ]);
    }

    /**
     * Apply stock to individual delivery.
     */
    private function applyStockToDelivery($delivery): void
    {
        $dateNow = Carbon::now();

        $stock = DB::table('delsched_stock')
            ->where('item_code', '=', $delivery->item_code)
            ->first();

        if ($delivery->status == 'success') {
            DB::table('delsched_final')
                ->where('id', $delivery->id)
                ->update([
                    'stock' => $stock->quantity,
                    'balance' => $stock->total_after,
                ]);
        } else {
            $outstanding = $delivery->outstanding;
            $totalAfter = $stock->total_after;

            if ($totalAfter < 0) {
                $newOutstanding = $outstanding;
                $newBalance = $totalAfter - $outstanding;
                $status = $delivery->delivery_date <= $dateNow ? 'danger' : 'light';
            } elseif ($totalAfter >= $outstanding) {
                $newOutstanding = 0;
                $newBalance = $totalAfter - $outstanding;
                $status = $delivery->delivery_date <= $dateNow ? 'warning' : 'light';
            } else {
                $newOutstanding = $outstanding - $totalAfter;
                $newBalance = $totalAfter - $outstanding;
                $status = $delivery->delivery_date <= $dateNow ? 'danger' : 'light';
            }

            DB::table('delsched_stock')
                ->where('id', $stock->id)
                ->update(['total_after' => $newBalance]);

            DB::table('delsched_final')
                ->where('id', $delivery->id)
                ->update([
                    'stock' => $stock->quantity,
                    'balance' => $newBalance,
                    'outstanding_stk' => $newOutstanding,
                    'status' => $status,
                ]);
        }
    }

    /**
     * Update last process time.
     */
    private function updateLastProcessTime(int $id): void
    {
        $now = new DateTime;
        $now->modify('+420 minutes');

        DB::table('uti_date_list')
            ->where('id', $id)
            ->update(['last_update' => $now]);
    }
}
