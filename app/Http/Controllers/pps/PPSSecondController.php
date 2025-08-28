<?php

namespace App\Http\Controllers\pps;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use DateTime;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProdplanScenario;
use App\Models\UtiDateList;
use App\Models\ProdplanSndDelitem;
use App\Models\ProdplanSndDelraw;
use App\Models\ProdplanSndDelsched;
use App\Models\ProdplanSndItem;
use App\Models\ProdplanSndLinelist;
use App\Models\ProdplanSndLinecap;
use App\Models\InvLineList;

use App\Models\MtcLineDown;
use App\Models\UtiHolidayList;

use Illuminate\Support\Facades\Date;

use App\DataTables\ProdplanSndDelschedDataTable;
use App\DataTables\ProdplanSndItemDataTable;
use App\DataTables\ProdplanSndLinecapDataTable;

class PPSSecondController extends Controller
{
    public function indexscenario()
    {
        $data = ProdplanScenario::get();
        $datedata = UtiDateList::get();

        DB::table("prodplan_snd_delitems")->truncate();
        DB::table("prodplan_snd_delraws")->truncate();
        DB::table("prodplan_snd_delscheds")->truncate();
        DB::table("prodplan_snd_items")->truncate();
        DB::table("prodplan_snd_linecaps")->truncate();
        DB::table("prodplan_snd_linelists")->truncate();
        return view("pps.secondindex", compact("data", "datedata"));
    }

    public function processSecondForm(Request $request)
    {
        // Validate the form data
        $data = ProdplanScenario::get();
        $datedata = UtiDateList::get();
        // dd($request->all());
        $validatedData = $request->validate([
            "start_date" => "required|date",
            "end_date" => "required|date",
            "hm_fg" => "required|integer",
            "hm_wip" => "required|integer",
            "jarak_gudang" => "required|integer",
            "max_manpower" => "required|integer",
            "max_mould_change" => "required|integer",
            "forecast" => "required",
            "count_wip" => "required",
        ]);

        // Process the form data
        // You can access the form data using $request->input('field_name')
        // For example:

        $datedata[15]->start_date = $request->input("start_date");
        $datedata[15]->end_date = $request->input("end_date");
        $data[0]->val_int_snd = $request->input("hm_fg");
        $data[1]->val_int_snd = $request->input("hm_wip");
        $data[2]->val_int_snd = $request->input("jarak_gudang");
        $data[3]->val_int_snd = $request->input("max_manpower");
        $data[4]->val_int_snd = $request->input("max_mould_change");
        $data[5]->val_int_snd = $request->input("forecast");
        $data[6]->val_int_snd = $request->input("count_wip");

        $datedata[15]->update(["start_date" => $request->input("start_date")]);
        $datedata[15]->update(["end_date" => $request->input("end_date")]);

        $data[0]->update(["val_int_snd" => $request->input("hm_fg")]);
        $data[1]->update(["val_int_snd" => $request->input("hm_wip")]);
        $data[2]->update(["val_int_snd" => $request->input("jarak_gudang")]);
        $data[3]->update(["val_int_snd" => $request->input("max_manpower")]);
        $data[4]->update(["val_int_snd" => $request->input("max_mould_change")]);
        $data[5]->update(["val_int_snd" => $request->input("forecast")]);
        $data[6]->update(["val_int_snd" => $request->input("count_wip")]);

        // Further processing or redirection goes here
        return redirect()->route("secondprocess1");
    }

    public function process1()
    {
        DB::table("prodplan_snd_delraws")->truncate();
        DB::table("prodplan_snd_delscheds")->truncate();
        DB::table("prodplan_snd_delitems")->truncate();
        DB::table("prodplan_snd_items")->truncate();

        $tab_scenario_lead_fg = DB::table("prodplan_scenarios")->where("id", 1)->first();
        $val_lead_fg = $tab_scenario_lead_fg->val_int_snd;

        $tab_scenario_lead_wip = DB::table("prodplan_scenarios")->where("id", 2)->first();
        $val_lead_wip = $tab_scenario_lead_wip->val_int_snd;

        $tab_date_list = DB::table("uti_date_list")->where("id", 16)->first();
        $val_start_date = $tab_date_list->start_date;
        $val_end_date = $tab_date_list->end_date;

        $val_past_date = (new Carbon($val_start_date))->addDays(-45);
        $val_advanced_date_fg = (new Carbon($val_end_date))->addDays($val_lead_fg);
        $val_advanced_date_wip = (new Carbon($val_end_date))->addDays($val_lead_wip);

        $tab_delsched_final = DB::table("delsched_final")
            ->where("outstanding_stk", ">", 0)
            ->whereBetween("delivery_date", [$val_past_date, $val_advanced_date_fg])
            ->get();

        foreach ($tab_delsched_final as $delsched_final) {
            $val_id_fg = $delsched_final->id;

            $tab_delsched_final_wip = DB::table("delsched_finalwip")
                ->where("fglink_id", $val_id_fg)
                ->first();

            if (empty($tab_delsched_final_wip->id)) {
                $val_departement = $delsched_final->departement;

                $val_item_code = $delsched_final->item_code;
                $val_delivery_date = $delsched_final->delivery_date;
                $val_bom_level = 0;
                $val_delivery_qty = $delsched_final->outstanding_stk;

                $tab_inventoryfg = DB::table("sap_inventory_fg")
                    ->where("item_code", $val_item_code)
                    ->first();
                $val_pair = $tab_inventoryfg->pair ?? "Default";

                if (empty($val_item_code)) {
                } else {
                    $ins_delraw = [
                        "delivery_date" => $val_delivery_date,
                        "bom_level" => $val_bom_level,
                        "item_code" => $val_item_code,
                        "item_pair" => $val_pair,
                        "asm_on_line" => "",
                        "fg_code_line" => "",
                        "quantity" => $val_delivery_qty,
                        "process_owner" => $val_departement,
                    ];
                    ProdplanSndDelraw::insert($ins_delraw);
                }
            } else {
                $tab_delsched_final_wip_link = DB::table("delsched_finalwip")
                    ->where("fglink_id", $val_id_fg)
                    ->where("departement", "361")
                    ->get();

                foreach ($tab_delsched_final_wip_link as $wip_link) {
                    $val_departement = $wip_link->departement;

                    $val_item_code = $wip_link->wip_code;
                    $val_delivery_date = $wip_link->delivery_date;
                    $val_bom_level = $wip_link->bom_level;
                    $val_delivery_qty = $wip_link->outstanding_wip;

                    $tab_inventoryfg = DB::table("sap_inventory_fg")
                        ->where("item_code", $val_item_code)
                        ->first();
                    $val_pair = $tab_inventoryfg->pair ?? "Default";
                }
                if ($val_delivery_qty > 0) {
                    if (empty($val_item_code)) {
                    } else {
                        $ins_delraw = [
                            "delivery_date" => $val_delivery_date,
                            "bom_level" => $val_bom_level,
                            "item_code" => $val_item_code,
                            "item_pair" => $val_pair,
                            "asm_on_line" => "",
                            "fg_code_line" => "",
                            "quantity" => $val_delivery_qty,
                            "process_owner" => $val_departement,
                        ];
                        ProdplanSndDelraw::insert($ins_delraw);
                    }
                }
            }
        }

        //Tarik ke delitem
        $tab_delraw_itemonly_paired = DB::table("prodplan_snd_delraws")
            ->select("item_code", "item_pair")
            ->distinct()
            ->get();

        foreach ($tab_delraw_itemonly_paired as $delraw_itemonly_paired) {
            $val_item_code = $delraw_itemonly_paired->item_code;
            $val_item_pair = $delraw_itemonly_paired->item_pair;

            if (empty($val_item_pair)) {
            } else {
                $ins_delitem = [
                    "item_code" => $val_item_code,
                    "item_pair" => $val_item_pair,
                ];
                ProdplanSndDelitem::insert($ins_delitem);
            }
        }

        $tab_delraw_itemonly_nopair = DB::table("prodplan_snd_delraws")
            ->select("item_code", "item_pair")
            ->distinct()
            ->get();

        foreach ($tab_delraw_itemonly_nopair as $delraw_itemonly_nopair) {
            $val_item_code_ii = $delraw_itemonly_nopair->item_code;
            $val_item_pair_ii = $delraw_itemonly_nopair->item_pair;

            $tab_delitem = DB::table("prodplan_snd_delitems")
                ->where("item_pair", $val_item_code_ii)
                ->first();

            if (empty($tab_delitem->item_code)) {
                if (empty($val_item_pair_ii)) {
                    $ins_delitem = [
                        "item_code" => $val_item_code_ii,
                        "item_pair" => $val_item_pair_ii,
                    ];
                    ProdplanSndDelitem::insert($ins_delitem);
                }
            }
        }

        return redirect()->route("secondprocess2");
    }

    public function process2()
    {
        $tab_delraw_date = DB::table("prodplan_snd_delraws")
            ->select("delivery_date")
            ->orderBy("delivery_date", "asc")
            ->distinct()
            ->get();

        foreach ($tab_delraw_date as $delraw_date) {
            $val_delivery_date = $delraw_date->delivery_date;

            $tab_delitem = DB::table("prodplan_snd_delitems")->get();

            foreach ($tab_delitem as $delitem) {
                $val_item_code = $delitem->item_code;

                $sum_del_qty = DB::table("prodplan_snd_delraws")
                    ->where("item_code", $val_item_code)
                    ->where("delivery_date", $val_delivery_date)
                    ->sum("quantity");

                if ($sum_del_qty > 0) {
                    if (empty($delitem->item_pair)) {
                        $cal_final_qty = $sum_del_qty;

                        $ins_delsched = [
                            "item_code" => $val_item_code,
                            "quantity" => $sum_del_qty,
                            "actual_deldate" => $val_delivery_date,
                            "final_quantity" => $cal_final_qty,
                        ];
                        ProdplanSndDelsched::insert($ins_delsched);
                    } else {
                        $val_pair_code = $delitem->item_pair;

                        $sum_del_pair_qty = DB::table("prodplan_snd_delraws")
                            ->where("item_code", $val_pair_code)
                            ->where("delivery_date", $val_delivery_date)
                            ->sum("quantity");

                        if ($sum_del_pair_qty >= $sum_del_qty) {
                            $cal_final_qty = $sum_del_pair_qty;
                        } else {
                            $cal_final_qty = $sum_del_qty;
                        }

                        $ins_delsched = [
                            "item_code" => $val_item_code,
                            "quantity" => $sum_del_qty,
                            "pair_code" => $val_pair_code,
                            "pair_quantity" => $sum_del_pair_qty,
                            "actual_deldate" => $val_delivery_date,
                            "final_quantity" => $cal_final_qty,
                        ];
                        ProdplanSndDelsched::insert($ins_delsched);
                    }
                } else {
                    if (empty($delitem->item_pair)) {
                    } else {
                        $val_pair_code = $delitem->item_pair;

                        $sum_del_pair_qty = DB::table("prodplan_snd_delraws")
                            ->where("item_code", $val_pair_code)
                            ->where("delivery_date", $val_delivery_date)
                            ->sum("quantity");

                        if ($sum_del_pair_qty >= $sum_del_qty) {
                            $cal_final_qty = $sum_del_pair_qty;
                        } else {
                            $cal_final_qty = $sum_del_qty;
                        }

                        if ($sum_del_pair_qty > 0) {
                            $ins_delsched = [
                                "item_code" => $val_item_code,
                                "quantity" => $sum_del_qty,
                                "pair_code" => $val_pair_code,
                                "pair_quantity" => $sum_del_pair_qty,
                                "actual_deldate" => $val_delivery_date,
                                "final_quantity" => $cal_final_qty,
                            ];
                            ProdplanSndDelsched::insert($ins_delsched);
                        }
                    }
                }
            }
        }

        return redirect()->route("secondprocess3");
    }

    public function process3()
    {
        $tab_delsched = DB::table("prodplan_snd_delscheds")->get();

        foreach ($tab_delsched as $delsched) {
            $val_delsched_id = $delsched->id;
            $val_item_code = $delsched->item_code;
            $tab_inventoryfg = DB::table("sap_inventory_fg")
                ->where("item_code", $val_item_code)
                ->first();

            $val_item_name = $tab_inventoryfg->item_name ?? "Default";
            $val_bom_level = $tab_inventoryfg->bom_level ?? "Default";

            if (empty($delsched->pair_code)) {
                $val_pair_code_up = null;
                $val_pair_name = null;
                $val_pair_bom_level = null;
                $val_prior_item_code = $val_item_code;
            } else {
                $val_pair_code = $delsched->pair_code;
                $tab_inventoryfg_pair = DB::table("sap_inventory_fg")
                    ->where("item_code", $val_pair_code)
                    ->first();

                if (empty($tab_inventoryfg_pair->item_name)) {
                    $val_pair_code_up = null;
                    $val_pair_name = null;
                    $val_pair_bom_level = null;
                    $val_prior_item_code = $val_item_code;
                } else {
                    $val_pair_code_up = $val_pair_code;
                    $val_pair_name = $tab_inventoryfg_pair->item_name;
                    $val_pair_bom_level = $tab_inventoryfg_pair->bom_level;
                    $val_prior_item_code = $val_pair_code;
                }
            }

            $tab_scen_lead_time_fg = DB::table("prodplan_scenarios")->where("id", 1)->first();
            $val_lead_time_fg = $tab_scen_lead_time_fg->val_int_snd;

            $tab_scen_lead_time_wip = DB::table("prodplan_scenarios")->where("id", 2)->first();
            $val_lead_time_wip = $tab_scen_lead_time_wip->val_int_snd;

            if (empty($val_pair_bom_level)) {
                $val_deldate = $delsched->actual_deldate;

                if ($val_bom_level < 1) {
                    $cal_lead_time_fg = -1 * $val_lead_time_fg;
                    $val_new_deldate = (new Carbon($val_deldate))->addDays($cal_lead_time_fg);
                    $val_prior_bom_level = $val_bom_level;
                    $val_lead_time = $val_lead_time_fg;
                } else {
                    $cal_lead_time_wip = -1 * $val_lead_time_wip;
                    $val_new_deldate = (new Carbon($val_deldate))->addDays($cal_lead_time_wip);
                    $val_prior_bom_level = $val_bom_level;
                    $val_lead_time = $val_lead_time_wip;
                }
            } else {
                if ($val_bom_level > $val_pair_bom_level) {
                    if ($val_bom_level < 1) {
                        $cal_lead_time_fg = -1 * $val_lead_time_fg;
                        $val_new_deldate = (new Carbon($val_deldate))->addDays($cal_lead_time_fg);
                        $val_prior_bom_level = $val_bom_level;
                        $val_lead_time = $val_lead_time_fg;
                    } else {
                        $cal_lead_time_wip = -1 * $val_lead_time_wip;
                        $val_new_deldate = (new Carbon($val_deldate))->addDays($cal_lead_time_wip);
                        $val_prior_bom_level = $val_bom_level;
                        $val_lead_time = $val_lead_time_wip;
                    }
                } else {
                    if ($val_pair_bom_level < 1) {
                        $cal_lead_time_fg = -1 * $val_lead_time_fg;
                        $val_new_deldate = (new Carbon($val_deldate))->addDays($cal_lead_time_fg);
                        $val_prior_bom_level = $val_pair_bom_level;
                        $val_lead_time = $val_lead_time_fg;
                    } else {
                        $cal_lead_time_wip = -1 * $val_lead_time_wip;
                        $val_new_deldate = (new Carbon($val_deldate))->addDays($cal_lead_time_wip);
                        $val_prior_bom_level = $val_pair_bom_level;
                        $val_lead_time = $val_lead_time_wip;
                    }
                }
            }

            $now = Carbon::now();

            if ($val_new_deldate < $now) {
                if ($val_deldate < $now) {
                    $val_color = "danger";
                } else {
                    $val_color = "warning";
                }
            } else {
                $val_color = "light";
            }

            //Update data di tabel delsched
            DB::table("prodplan_snd_delscheds")
                ->where("id", $val_delsched_id)
                ->update([
                    "delivery_date" => $val_new_deldate,
                    "item_name" => $val_item_name,
                    "item_bom_level" => $val_bom_level,
                    "pair_code" => $val_pair_code_up,
                    "pair_name" => $val_pair_name,
                    "pair_bom_level" => $val_pair_bom_level,
                    "prior_item_code" => $val_prior_item_code,
                    "prior_bom_level" => $val_prior_bom_level,
                    "completed" => 0,
                    "outstanding" => $delsched->final_quantity,
                    "status" => 0,
                    "remarks" => "Not Completed",
                    "remarks_leadtime" => $val_lead_time,
                    "color" => $val_color,
                ]);
        }

        return redirect()->route("deliverysecond");
    }

    public function deliverysecond(ProdplanSndDelschedDataTable $dataTable)
    {
        return $dataTable->render("pps.seconddelivery");
        // return view("pps.seconddelivery");
    }

    public function process4()
    {
        DB::table("prodplan_snd_linelists")->truncate();
        DB::table("prodplan_snd_linecaps")->truncate();

        $tab_delsched_itemonly = DB::table("prodplan_snd_delscheds")
            ->select("item_code")
            ->distinct()
            ->get();

        foreach ($tab_delsched_itemonly as $delsched_itemonly) {
            $ins_items = [
                "item_code" => $delsched_itemonly->item_code,
            ];
            ProdplanSndItem::insert($ins_items);
        }

        $tab_items = DB::table("prodplan_snd_items")->get();

        foreach ($tab_items as $items) {
            $val_items_id = $items->id;
            $val_item_code = $items->item_code;
            $tab_delsched = DB::table("prodplan_snd_delscheds")
                ->where("item_code", $val_item_code)
                ->first();
            $val_pair_code = $tab_delsched->pair_code;
            $val_bom_level = $tab_delsched->prior_bom_level;
            $val_lead_time = $tab_delsched->remarks_leadtime;

            $sum_outstanding = DB::table("prodplan_snd_delscheds")
                ->where("item_code", $val_item_code)
                ->sum("outstanding");

            //Update data di tabel items
            DB::table("prodplan_snd_items")
                ->where("id", $val_items_id)
                ->update([
                    "pair_code" => $val_pair_code,
                    "bom_level" => $val_bom_level,
                    "lead_time" => $val_lead_time,
                    "total_delivery" => $sum_outstanding,
                ]);
        }

        $tab_items_up = DB::table("prodplan_snd_items")->get();

        foreach ($tab_items as $items) {
            $val_items_id = $items->id;

            if (empty($items->pair_code)) {
                $val_prior_item = $items->item_code;
            } else {
                $val_prior_item = $items->pair_code;
            }

            $tab_inventory_fg = DB::table("sap_inventory_fg")
                ->where("item_code", $val_prior_item)
                ->first();

            $val_continue_prod = $tab_inventory_fg->continue_production;
            $val_cycle_time_raw = $tab_inventory_fg->cycle_time;
            $val_daily_limit = $tab_inventory_fg->daily_limit;
            $val_prod_min = $tab_inventory_fg->production_min_qty;
            $val_cavity = $tab_inventory_fg->cavity;
            $val_safety_stock = $tab_inventory_fg->safety_stock;

            //Update data di tabel items
            DB::table("prodplan_snd_items")
                ->where("id", $val_items_id)
                ->update([
                    "continue_prod" => $val_continue_prod,
                    "safety_stock" => $val_safety_stock,
                    "daily_limit" => $val_daily_limit,
                    "prod_min" => $val_prod_min,
                    "cycle_time_raw" => $val_cycle_time_raw,
                    "cavity" => $val_cavity,
                ]);
        }

        return redirect()->route("itemsecond");
    }

    public function itemsecond(ProdplanSndItemDataTable $dataTable)
    {
        // return view("pps.seconditem");
        return $dataTable->render("pps.seconditem");
    }

    public function process5()
    {
        DB::table("prodplan_snd_linelists")->truncate();

        $data = InvLineList::where("departement", 361)->get();
        // dd($data);

        foreach ($data as $item) {
            ProdplanSndLinelist::create([
                "area" => $item->area,
                "line_code" => $item->line_code,
                "daily_minutes" => $item->daily_minutes,
                "continue_running" => $item->continue_running ?? 0,
                "status" => "START",
            ]);
        }

        $datadate = MtcLineDown::where("line_code", "line_code")->get();

        foreach ($datadate as $item) {
            ProdplanSndLinelist::update([
                "start_repair" => $item->date_down,
                "end_repair" => $item->date_prediction,
            ]);
        }

        return redirect()->route("secondprocess6");
    }

    public function process6()
    {
        DB::table("prodplan_snd_linecaps")->truncate();
        $date = UtiDateList::find(16);
        $holiday = UtiHolidayList::get();
        $data = ProdplanSndLinelist::get();

        // dd($data);

        // Assuming $date is the instance of UtiDateList
        $start_date = Carbon::parse($date->start_date);
        $end_date = Carbon::parse($date->end_date);

        $dates = [];

        // Loop through each date from start_date to end_date
        for ($current_date = $start_date; $current_date->lte($end_date); $current_date->addDay()) {
            $dates[] = $current_date->format("Y-m-d");
        }

        foreach ($holiday as $holidayItem) {
            foreach ($dates as $key => $date) {
                if ($date == $holidayItem->date && $holidayItem->half_day == 0) {
                    unset($dates[$key]);
                }
            }
        }
        // dd($dates);

        foreach ($data as $item) {
            foreach ($dates as $date) {
                $lineCap = new ProdplanSndLinecap();
                $lineCap->line_code = $item->line_code; // Assuming line_code is a field in both models
                $lineCap->running_date = $date; // Set the running date from $dates
                $department = InvLineList::where("line_code", $item->line_code)->value(
                    "departement",
                );

                // Check if the running_date is a holiday with half_day = 1
                $holiday = UtiHolidayList::where("date", $date)->where("half_day", 1)->exists();
                // dd($holiday);

                $eachTimeLimit = $item->daily_minutes / 3;

                if ($holiday) {
                    // If it's a holiday with half_day = 1, adjust the daily_minutes
                    $lineCap->time_limit_all = $item->daily_minutes / 2;
                    $lineCap->time_limit_one = $eachTimeLimit / 2;
                    $lineCap->time_limit_two = $eachTimeLimit / 2;
                    $lineCap->time_limit_three = $eachTimeLimit / 2;
                } else {
                    $lineCap->time_limit_all = $item->daily_minutes;
                    $lineCap->time_limit_one = $eachTimeLimit;
                    $lineCap->time_limit_two = $eachTimeLimit;
                    $lineCap->time_limit_three = $eachTimeLimit;
                }

                if ($department) {
                    $lineCap->departement = $department;
                } else {
                    // If the department is not found, handle the situation accordingly
                    // For example, set a default value or log an error
                    $lineCap->departement = "Default Department";
                }

                $lineDown = MtcLineDown::where("line_code", $item->line_code)
                    ->where("date_down", "<=", $date)
                    ->where("date_prediction", ">=", $date)
                    ->exists();

                if ($lineDown) {
                    // If line_code matches and falls within the date range, set time_limit_all, one, two, and three to 0
                    $lineCap->time_limit_all = 0;
                    $lineCap->time_limit_one = 0;
                    $lineCap->time_limit_two = 0;
                    $lineCap->time_limit_three = 0;
                }

                $lineCap->save();
            }
        }
        return redirect()->route("linesecond");
    }

    public function linesecond(ProdplanSndLinecapDataTable $dataTable)
    {
        return $dataTable->render("pps.secondline");
        // return view("pps.secondline");
    }

    public function finalresultsecond()
    {
        return view("pps.finalresultppssecond");
    }
}
