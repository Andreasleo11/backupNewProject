<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DateTime;

class DeliveryScheduleService
{
    public function processAllSteps()
    {
        DB::transaction(function () {
            $this->runStep1();
            $this->runStep2();
            $this->runStep3();
            $this->runStep4();
        });
    }

    public function runStep1()
    {
        DB::table("delsched_final")->truncate();
        DB::table("delsched_solist")->truncate();
        DB::table("delsched_delfilter")->truncate();
        DB::table("delsched_delsum")->truncate();
        DB::table("delsched_stock")->truncate();
        DB::table("delsched_finalwip")->truncate();
        DB::table("delsched_stockwip")->truncate();

        $this->insertDelschedFinal();
        $this->insertDelschedSolist();
        $this->insertDelschedDelfilter();
        $this->insertDelschedDelsum();
    }

    private function insertDelschedFinal()
    {
        DB::table("sap_delsched")
            ->orderBy("delivery_date")
            ->orderBy("item_code")
            ->chunk(500, function ($records) {
                $insertData = [];

                foreach ($records as $row) {
                    $inventory = DB::table("sap_inventory_fg")
                        ->where("item_code", $row->item_code)
                        ->first();
                    if (!$inventory) {
                        Log::error("No sap_inventory_fg found for item_code: {$row->item_code}");
                        continue;
                    }

                    $customer = DB::table("sap_fg_customers")
                        ->where("item_code", $row->item_code)
                        ->first();
                    $dept = match ($inventory->process_owner) {
                        "INJ" => 390,
                        "SEC" => 361,
                        default => 362,
                    };

                    $insertData[] = [
                        "delivery_date" => $row->delivery_date,
                        "item_code" => $row->item_code,
                        "item_name" => $inventory->item_name,
                        "delivery_qty" => $row->delivery_qty,
                        "so_number" => $row->so_number,
                        "doc_status" => "O",
                        "packaging_code" => $inventory->packaging,
                        "standar_pack" => $inventory->standar_packing,
                        "customer_code" => $customer->customer_code ?? "",
                        "customer_name" => $customer->customer_name ?? "",
                        "departement" => $dept,
                    ];
                }

                if (!empty($insertData)) {
                    DB::table("delsched_final")->insert($insertData);
                }
            });
    }

    private function insertDelschedSolist()
    {
        DB::table("sap_delso")
            ->orderBy("doc_num")
            ->orderBy("item_no")
            ->chunk(500, function ($records) {
                $insertData = [];

                foreach ($records as $row) {
                    $insertData[] = [
                        "so_number" => $row->doc_num,
                        "so_status" => $row->doc_status,
                        "item_code" => $row->item_no,
                        "so_qty" => $row->quantity,
                        "delivered_qty" => $row->delivered_qty,
                        "row_status" => $row->row_status,
                    ];
                }

                if (!empty($insertData)) {
                    DB::table("delsched_solist")->insert($insertData);
                }
            });
    }

    private function insertDelschedDelfilter()
    {
        $records = DB::table("sap_delactual")->get();

        foreach ($records as $row) {
            $so = DB::table("delsched_solist")->where("so_number", $row->so_num)->first();
            $status = $so->so_status ?? "O";

            if ($status === "O") {
                DB::table("delsched_delfilter")->insert([
                    "item_code" => $row->item_no,
                    "delivery_date" => $row->delivery_date,
                    "quantity" => $row->quantity,
                    "so_number" => $row->so_num,
                ]);
            }
        }
    }

    private function insertDelschedDelsum()
    {
        $items = DB::table("delsched_delfilter")->select("item_code")->distinct()->get();

        foreach ($items as $item) {
            $sum = DB::table("delsched_delfilter")
                ->where("item_code", $item->item_code)
                ->sum("quantity");

            DB::table("delsched_delsum")->insert([
                "item_code" => $item->item_code,
                "quantity" => $sum,
                "total_after" => $sum,
            ]);
        }
    }

    public function runStep2()
    {
        $records = DB::table("delsched_final")->where("so_number", "<>", "")->get();

        foreach ($records as $row) {
            $so = DB::table("delsched_solist")->where("so_number", $row->so_number)->first();

            if ($so && $so->so_status === "C") {
                DB::table("delsched_final")
                    ->where("id", $row->id)
                    ->update([
                        "delivered" => $row->delivery_qty,
                        "outstanding" => 0,
                        "outstanding_stk" => 0,
                        "doc_status" => "C",
                        "status" => "success",
                    ]);
            }
        }
    }

    public function runStep3()
    {
        $records = DB::table("delsched_final")->where("doc_status", "O")->get();

        foreach ($records as $row) {
            $sum = DB::table("delsched_delsum")->where("item_code", $row->item_code)->first();
            $available = $sum->total_after ?? 0;

            if ($available <= 0) {
                DB::table("delsched_final")
                    ->where("id", $row->id)
                    ->update([
                        "delivered" => 0,
                        "outstanding" => $row->delivery_qty,
                        "outstanding_stk" => $row->delivery_qty,
                        "status" => "danger",
                    ]);
            } elseif ($available >= $row->delivery_qty) {
                DB::table("delsched_delsum")
                    ->where("id", $sum->id)
                    ->update([
                        "total_after" => $available - $row->delivery_qty,
                    ]);

                DB::table("delsched_final")
                    ->where("id", $row->id)
                    ->update([
                        "delivered" => $row->delivery_qty,
                        "outstanding" => 0,
                        "outstanding_stk" => 0,
                        "status" => "success",
                    ]);
            } else {
                $delivered = $available;
                $outstanding = $row->delivery_qty - $available;

                DB::table("delsched_delsum")
                    ->where("id", $sum->id)
                    ->update([
                        "total_after" => 0,
                    ]);

                DB::table("delsched_final")
                    ->where("id", $row->id)
                    ->update([
                        "delivered" => $delivered,
                        "outstanding" => $outstanding,
                        "outstanding_stk" => $outstanding,
                        "status" => "warning",
                    ]);
            }
        }
    }

    public function runStep4()
    {
        $items = DB::table("delsched_final")->select("item_code")->distinct()->get();

        foreach ($items as $item) {
            $stock = DB::table("sap_inventory_fg")->where("item_code", $item->item_code)->first();
            DB::table("delsched_stock")->insert([
                "item_code" => $item->item_code,
                "quantity" => $stock->stock,
                "total_after" => $stock->stock,
            ]);
        }

        $records = DB::table("delsched_final")->orderBy("id")->get();
        $now = Carbon::now();

        foreach ($records as $row) {
            $stock = DB::table("delsched_stock")->where("item_code", $row->item_code)->first();
            $status = $row->status;
            $deliveryDate = Carbon::parse($row->delivery_date);

            if ($status === "success") {
                DB::table("delsched_final")
                    ->where("id", $row->id)
                    ->update([
                        "stock" => $stock->quantity,
                        "balance" => $stock->total_after,
                    ]);
                continue;
            }

            $outstanding = $row->outstanding;
            $after = $stock->total_after;
            $newAfter = $after - $outstanding;
            $newStatus = $deliveryDate->lessThanOrEqualTo($now)
                ? ($after >= $outstanding
                    ? "warning"
                    : "danger")
                : "light";

            DB::table("delsched_stock")
                ->where("id", $stock->id)
                ->update([
                    "total_after" => $newAfter,
                ]);

            DB::table("delsched_final")
                ->where("id", $row->id)
                ->update([
                    "stock" => $stock->quantity,
                    "balance" => $newAfter,
                    "outstanding_stk" => $after >= $outstanding ? 0 : $outstanding - $after,
                    "status" => $newStatus,
                ]);
        }

        DB::table("uti_date_list")
            ->where("id", 13)
            ->update([
                "updated_at" => now()->addMinutes(420),
            ]);
    }
}
