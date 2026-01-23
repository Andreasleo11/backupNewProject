<?php

declare(strict_types=1);

namespace App\Domain\Production\Services;

use App\Models\prodplan_inj_delitem;
use App\Models\prodplan_inj_delraw;
use App\Models\prodplan_inj_delsched;
use App\Models\prodplan_inj_items;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class ProductionPlanningService
{
    /**
     * Process delivery schedule for production planning.
     */
    public function processDeliverySchedule(): void
    {
        DB::table('prodplan_inj_delraw')->truncate();
        DB::table('prodplan_inj_delsched')->truncate();
        DB::table('prodplan_inj_delitem')->truncate();
        DB::table('prodplan_inj_items')->truncate();

        $leadTimes = $this->getLeadTimes();
        $dateRange = $this->getDateRange($leadTimes);

        $this->pullDeliveryData($dateRange);
        $this->createItemPairings();
        $this->aggregateDeliverySchedule();
        $this->enrichScheduleData($leadTimes);
    }

    /**
     * Process production items.
     */
    public function processProductionItems(): void
    {
        DB::table('prodplan_inj_items')->truncate();

        // Create items from schedule
        $items = DB::table('prodplan_inj_delsched')
            ->select('item_code')
            ->distinct()
            ->get();

        foreach ($items as $item) {
            prodplan_inj_items::insert(['item_code' => $item->item_code]);
        }

        // Enrich with schedule data
        $this->enrichItemsWithScheduleData();

        // Enrich with inventory data
        $this->enrichItemsWithInventoryData();
    }

    /**
     * Get lead times from scenario.
     */
    private function getLeadTimes(): array
    {
        $leadFg = DB::table('prodplan_scenario')->where('id', 1)->first();
        $leadWip = DB::table('prodplan_scenario')->where('id', 2)->first();

        return [
            'fg' => $leadFg->val_int_inj,
            'wip' => $leadWip->val_int_inj,
        ];
    }

    /**
     * Get date range for planning.
     */
    private function getDateRange(array $leadTimes): array
    {
        $dateList = DB::table('uti_date_list')->where('id', 15)->first();

        return [
            'start' => $dateList->start_date,
            'end' => $dateList->end_date,
            'past' => (new Carbon($dateList->start_date))->addDays(-45),
            'future_fg' => (new Carbon($dateList->end_date))->addDays($leadTimes['fg']),
            'future_wip' => (new Carbon($dateList->end_date))->addDays($leadTimes['wip']),
        ];
    }

    /**
     * Pull delivery data from delsched_final.
     */
    private function pullDeliveryData(array $dateRange): void
    {
        $deliveries = DB::table('delsched_final')
            ->where('outstanding_stk', '>', 0)
            ->whereBetween('delivery_date', [$dateRange['past'], $dateRange['future_fg']])
            ->get();

        foreach ($deliveries as $delivery) {
            $wipLink = DB::table('delsched_finalwip')
                ->where('fglink_id', $delivery->id)
                ->first();

            if (empty($wipLink->id)) {
                $this->insertFGDeliveryRaw($delivery);
            } else {
                $this->insertWIPDeliveryRaw($delivery->id);
            }
        }
    }

    /**
     * Insert FG delivery to raw table.
     */
    private function insertFGDeliveryRaw($delivery): void
    {
        $inventory = DB::table('sap_inventory_fg')
            ->where('item_code', $delivery->item_code)
            ->first();

        prodplan_inj_delraw::insert([
            'delivery_date' => $delivery->delivery_date,
            'bom_level' => 0,
            'item_code' => $delivery->item_code,
            'item_pair' => $inventory->pair ?? 'Default',
            'asm_on_line' => '',
            'fg_code_line' => '',
            'quantity' => $delivery->outstanding_stk,
            'process_owner' => $delivery->departement,
        ]);
    }

    /**
     * Insert WIP delivery to raw table.
     */
    private function insertWIPDeliveryRaw(int $fgLinkId): void
    {
        $wipLinks = DB::table('delsched_finalwip')
            ->where('fglink_id', $fgLinkId)
            ->where('departement', '390')
            ->get();

        foreach ($wipLinks as $wip) {
            if ($wip->outstanding_wip > 0) {
                $inventory = DB::table('sap_inventory_fg')
                    ->where('item_code', $wip->wip_code)
                    ->first();

                prodplan_inj_delraw::insert([
                    'delivery_date' => $wip->delivery_date,
                    'bom_level' => $wip->bom_level,
                    'item_code' => $wip->wip_code,
                    'item_pair' => $inventory->pair ?? 'Default',
                    'asm_on_line' => '',
                    'fg_code_line' => '',
                    'quantity' => $wip->outstanding_wip,
                    'process_owner' => $wip->departement,
                ]);
            }
        }
    }

    /**
     * Create item pairings.
     */
    private function createItemPairings(): void
    {
        $items = DB::table('prodplan_inj_delraw')
            ->select('item_code', 'item_pair')
            ->distinct()
            ->get();

        foreach ($items as $item) {
            if (! empty($item->item_pair)) {
                prodplan_inj_delitem::insert([
                    'item_code' => $item->item_code,
                    'item_pair' => $item->item_pair,
                ]);
            }
        }

        // Add non-paired items
        foreach ($items as $item) {
            $existing = DB::table('prodplan_inj_delitem')
                ->where('item_pair', $item->item_code)
                ->first();

            if (empty($existing->item_code) && empty($item->item_pair)) {
                prodplan_inj_delitem::insert([
                    'item_code' => $item->item_code,
                    'item_pair' => $item->item_pair,
                ]);
            }
        }
    }

    /**
     * Aggregate delivery schedule by date and item.
     */
    private function aggregateDeliverySchedule(): void
    {
        $dates = DB::table('prodplan_inj_delraw')
            ->select('delivery_date')
            ->orderBy('delivery_date', 'asc')
            ->distinct()
            ->get();

        foreach ($dates as $date) {
            $items = DB::table('prodplan_inj_delitem')->get();

            foreach ($items as $item) {
                $qty = DB::table('prodplan_inj_delraw')
                    ->where('item_code', $item->item_code)
                    ->where('delivery_date', $date->delivery_date)
                    ->sum('quantity');

                if ($qty > 0 || ! empty($item->item_pair)) {
                    $this->createScheduleEntry($item, $date->delivery_date, $qty);
                }
            }
        }
    }

    /**
     * Create schedule entry with pair calculations.
     */
    private function createScheduleEntry($item, string $date, float $qty): void
    {
        if (empty($item->item_pair)) {
            prodplan_inj_delsched::insert([
                'item_code' => $item->item_code,
                'quantity' => $qty,
                'actual_deldate' => $date,
                'final_quantity' => $qty,
            ]);
        } else {
            $pairQty = DB::table('prodplan_inj_delraw')
                ->where('item_code', $item->item_pair)
                ->where('delivery_date', $date)
                ->sum('quantity');

            prodplan_inj_delsched::insert([
                'item_code' => $item->item_code,
                'quantity' => $qty,
                'pair_code' => $item->item_pair,
                'pair_quantity' => $pairQty,
                'actual_deldate' => $date,
                'final_quantity' => max($qty, $pairQty),
            ]);
        }
    }

    /**
     * Enrich schedule with lead times and status.
     */
    private function enrichScheduleData(array $leadTimes): void
    {
        $schedules = DB::table('prodplan_inj_delsched')->get();

        foreach ($schedules as $schedule) {
            $enrichedData = $this->calculateScheduleEnrichment($schedule, $leadTimes);

            DB::table('prodplan_inj_delsched')
                ->where('id', $schedule->id)
                ->update($enrichedData);
        }
    }

    /**
     * Calculate enrichment data for schedule.
     */
    private function calculateScheduleEnrichment($schedule, array $leadTimes): array
    {
        $inventory = DB::table('sap_inventory_fg')
            ->where('item_code', $schedule->item_code)
            ->first();

        $bomLevel = $inventory->bom_level ?? 0;
        $leadTime = $bomLevel < 1 ? $leadTimes['fg'] : $leadTimes['wip'];
        $newDate = (new Carbon($schedule->actual_deldate))->addDays(-1 * $leadTime);

        $now = Carbon::now();
        $color = match (true) {
            $newDate < $now && $schedule->actual_deldate < $now => 'danger',
            $newDate < $now => 'warning',
            default => 'light',
        };

        return [
            'delivery_date' => $newDate,
            'item_name' => $inventory->item_name ?? 'Default',
            'item_bom_level' => $bomLevel,
            'prior_item_code' => $schedule->pair_code ?? $schedule->item_code,
            'prior_bom_level' => $bomLevel,
            'completed' => 0,
            'outstanding' => $schedule->final_quantity,
            'status' => 0,
            'remarks' => 'Not Completed',
            'remarks_leadtime' => $leadTime,
            'color' => $color,
        ];
    }

    /**
     * Enrich items with schedule data.
     */
    private function enrichItemsWithScheduleData(): void
    {
        $items = DB::table('prodplan_inj_items')->get();

        foreach ($items as $item) {
            $schedule = DB::table('prodplan_inj_delsched')
                ->where('item_code', $item->item_code)
                ->first();

            $totalDelivery = DB::table('prodplan_inj_delsched')
                ->where('item_code', $item->item_code)
                ->sum('outstanding');

            DB::table('prodplan_inj_items')
                ->where('id', $item->id)
                ->update([
                    'pair_code' => $schedule->pair_code,
                    'bom_level' => $schedule->prior_bom_level,
                    'lead_time' => $schedule->remarks_leadtime,
                    'total_delivery' => $totalDelivery,
                ]);
        }
    }

    /**
     * Enrich items with inventory data.
     */
    private function enrichItemsWithInventoryData(): void
    {
        $items = DB::table('prodplan_inj_items')->get();

        foreach ($items as $item) {
            $priorItem = $item->pair_code ?? $item->item_code;

            $inventory = DB::table('sap_inventory_fg')
                ->where('item_code', $priorItem)
                ->first();

            DB::table('prodplan_inj_items')
                ->where('id', $item->id)
                ->update([
                    'continue_prod' => $inventory->continue_production,
                    'safety_stock' => $inventory->safety_stock,
                    'daily_limit' => $inventory->daily_limit,
                    'prod_min' => $inventory->production_min_qty,
                    'cycle_time_raw' => $inventory->cycle_time,
                    'cavity' => $inventory->cavity,
                ]);
        }
    }
}
