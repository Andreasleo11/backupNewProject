<?php

declare(strict_types=1);

namespace App\Domain\Production\Services;

use App\Models\delsched_finalwip;
use App\Models\delsched_stockwip;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;

final class WIPProcessingService
{
    /**
     * Process WIP (Work in Progress) data - Step 1.
     */
    public function processWIPData(): void
    {
        DB::table('delsched_finalwip')->truncate();
        DB::table('delsched_stockwip')->truncate();

        $deliveries = DB::table('delsched_final')
            ->where('status', '=', 'danger')
            ->orWhere('status', '=', 'light')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($deliveries as $delivery) {
            $this->processWIPForDelivery($delivery);
        }
    }

    /**
     * Process WIP final calculations - Step 2.
     */
    public function processWIPFinalCalculations(): void
    {
        // Create stock records for WIP items
        $wipItems = DB::table('delsched_finalwip')
            ->select('wip_code')
            ->distinct()
            ->get();

        foreach ($wipItems as $item) {
            $inventory = DB::table('sap_inventory_fg')
                ->where('item_code', '=', $item->wip_code)
                ->first();

            delsched_stockwip::insert([
                'item_code' => $item->wip_code,
                'quantity' => $inventory->stock,
                'total_after' => $inventory->stock,
            ]);
        }

        // Apply stock to WIP deliveries
        $wipDeliveries = DB::table('delsched_finalwip')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($wipDeliveries as $wip) {
            $this->applyStockToWIP($wip);
        }

        // Update last update timestamp
        $this->updateLastProcessTime(14);
    }

    /**
     * Process WIP for individual delivery.
     */
    private function processWIPForDelivery($delivery): void
    {
        $bomCheck = DB::table('sap_bom_wip')
            ->where('fg_code', '=', $delivery->item_code)
            ->first();

        if (empty($bomCheck->fg_code)) {
            return;
        }

        $bomRecords = DB::table('sap_bom_wip')
            ->where('fg_code', '=', $delivery->item_code)
            ->get();

        foreach ($bomRecords as $bom) {
            $wipData = $this->calculateWIPRequirements($bom, $delivery->outstanding_stk);

            $inventory = DB::table('sap_inventory_fg')
                ->where('item_code', '=', $wipData['wip_code'])
                ->first();

            $departement = match ($inventory->process_owner) {
                'INJ' => 390,
                'SEC' => 361,
                default => 362,
            };

            delsched_finalwip::insert([
                'fglink_id' => $delivery->id,
                'delivery_date' => $delivery->delivery_date,
                'so_number' => $delivery->so_number,
                'customer_code' => $delivery->customer_code,
                'customer_name' => $delivery->customer_name,
                'item_code' => $delivery->item_code,
                'item_name' => $delivery->item_name,
                'outstanding_del' => $delivery->outstanding_stk,
                'wip_code' => $wipData['wip_code'],
                'wip_name' => $inventory->item_name,
                'departement' => $departement,
                'bom_level' => $wipData['level'],
                'bom_quantity' => $wipData['bom_qty'],
                'req_quantity' => $wipData['req_qty'],
                'stock_wip' => $inventory->stock,
                'balance_wip' => $inventory->stock,
                'status' => 'light',
            ]);
        }
    }

    /**
     * Calculate WIP requirements based on BOM level.
     */
    private function calculateWIPRequirements($bom, float $outstanding): array
    {
        $level = $bom->level;

        if ($level == 3) {
            $bomQty = $bom->qty_first * $bom->qty_second * $bom->qty_third;
            $reqQty = $bomQty * $outstanding;
            $wipCode = $bom->semi_third;
        } elseif ($level == 2) {
            $bomQty = $bom->qty_first * $bom->qty_second;
            $reqQty = $bomQty * $outstanding;
            $wipCode = $bom->semi_second;
        } else {
            $bomQty = $bom->qty_first;
            $reqQty = $bomQty * $outstanding;
            $wipCode = $bom->semi_first;
        }

        return [
            'level' => $level,
            'bom_qty' => $bomQty,
            'req_qty' => $reqQty,
            'wip_code' => $wipCode,
        ];
    }

    /**
     * Apply stock to WIP delivery.
     */
    private function applyStockToWIP($wip): void
    {
        $dateNow = Carbon::now();

        $stock = DB::table('delsched_stockwip')
            ->where('item_code', '=', $wip->wip_code)
            ->first();

        $totalAfter = $stock->total_after;
        $reqQty = $wip->req_quantity;

        if ($totalAfter < 0) {
            $newOutstanding = $reqQty;
            $newBalance = $totalAfter - $reqQty;
            $status = $wip->delivery_date <= $dateNow ? 'danger' : 'light';
        } elseif ($totalAfter >= $reqQty) {
            $newOutstanding = 0;
            $newBalance = $totalAfter - $reqQty;
            $status = $wip->delivery_date <= $dateNow ? 'danger' : 'light';
        } else {
            $newOutstanding = $reqQty - $totalAfter;
            $newBalance = $totalAfter - $reqQty;
            $status = $wip->delivery_date <= $dateNow ? 'danger' : 'light';
        }

        DB::table('delsched_stockwip')
            ->where('id', $stock->id)
            ->update(['total_after' => $newBalance]);

        DB::table('delsched_finalwip')
            ->where('id', $wip->id)
            ->update([
                'stock_wip' => $stock->quantity,
                'balance_wip' => $newBalance,
                'outstanding_wip' => $newOutstanding,
                'status' => $status,
            ]);
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
