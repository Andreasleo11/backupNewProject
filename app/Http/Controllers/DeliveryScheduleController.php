<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 100000);

use App\DataTables\DeliveryNewTableDataTable;
use App\DataTables\SapDelschedDataTable;
use App\DataTables\WipFinalDsDataTable;
use App\Jobs\ProcessDeliverySchedule;
use App\Jobs\StartDeliveryScheduleProcessing;
use App\Models\delsched_final;
use App\Models\delsched_finalwip;
use App\Models\delsched_stockwip;
use App\Models\DelschedFinal;
use App\Models\DelschedFinalWip;
use App\Models\SapDelsched;
use App\Models\SapInventoryFg;
use App\Models\SapInventoryMtr;
use App\Models\SapReject;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;

class DeliveryScheduleController extends Controller
{
    public function index(DeliveryNewTableDataTable $dataTable)
    {
        // $datas = DelschedFinal::paginate(10);

        // foreach($datas as $data)
        // {
        //     dd($data);
        // }

        return $dataTable->render('business.dsnewindex');
    }

    public function indexfinal(WipFinalDsDataTable $dataTable)
    {
        // $datas = DelschedFinalWip::paginate(10);

        // foreach($datas as $data)
        // {
        //     dd($data);
        // }

        return $dataTable->render('business.dsnewindexwip');
    }

    public function indexraw(SapDelschedDataTable $dataTable)
    {
        // $datas = DelschedFinalWip::paginate(10);

        // foreach($datas as $data)
        // {
        //     dd($data);
        // }

        return $dataTable->render('business.rawdelsched');
    }

    public function averageschedule()
    {
        $data = SapDelsched::all();

        $rejectdatas = SapReject::all();

        // Mengelompokkan data berdasarkan bulan dan item_code, kemudian menghitung total quantity
        $today = now();
        $currentMonth = $today->format('Y-m');

        // Filter the data to only include the current month
        $data = $data->filter(function ($item) use ($currentMonth) {
            return Carbon::parse($item->delivery_date)->format('Y-m') === $currentMonth;
        });

        // Group the data by month and item_code, then calculate total quantity
        $itemCounts = $data
            ->groupBy(function ($item) {
                return Carbon::parse($item->delivery_date)->format('Y-m');
            })
            ->map(function ($group) {
                return $group->groupBy('item_code')->map(function ($itemGroup) {
                    // Count unique item codes
                    return $itemGroup->count();
                });
            });

        // Fetch the SapInventoryMtr data
        $inventoryData = SapInventoryFg::all();

        // Map the inventory data based on fg_code
        $inventoryMap = $inventoryData->keyBy('item_code');

        // Combine the grouped data with the inventory data
        $result = $itemCounts->map(function ($group) use ($inventoryMap, $rejectdatas) {
            return $group->map(function ($count, $itemCode) use ($inventoryMap, $rejectdatas) {
                $inventory = $inventoryMap->get($itemCode);

                // Initialize an array to hold stock and item_name
                $inventoryInfo = [
                    'in_stock' => null,
                    'item_name' => null,
                    'warehouse' => null,
                ];

                // If inventory data exists, populate stock and item_name
                if ($inventory) {
                    $inventoryInfo['in_stock'] = $inventory->stock;
                    // Assuming item_name is a field in the inventory model or related model
                    $inventoryInfo['item_name'] = $inventory->item_name; // Adjust as per your actual field name
                    $inventoryInfo['warehouse'] = $inventory->warehouse;

                    foreach ($rejectdatas as $reject) {
                        if ($reject->item_no === $inventory->item_code) {
                            $inventoryInfo['in_stock'] -= $reject->in_stock; // Adjust field name as needed
                        }
                    }
                }

                return $inventoryInfo;
            });
        });

        // dd($result);
        // // Calculate total quantities for the current month
        $totalQuantities = $data
            ->groupBy(function ($item) {
                return Carbon::parse($item->delivery_date)->format('Y-m');
            })
            ->map(function ($group) {
                return $group->groupBy('item_code')->map(function ($itemGroup) {
                    return $itemGroup->sum('delivery_qty');
                });
            });
        // dd($totalQuantities);

        return view(
            'business.averageschedule',
            compact('data', 'itemCounts', 'totalQuantities', 'result'),
        );
    }

    public function step1()
    {
        // ProcessDeliverySchedule::dispatch();
        StartDeliveryScheduleProcessing::dispatchChain();

        return redirect()->route('indexds');
    }

    public function step1wip()
    {
        // 0. Format data dalam table tertentu
        DB::table('delsched_finalwip')->truncate();
        DB::table('delsched_stockwip')->truncate();

        // 1. Menarik data dari final menjadi wip ke dalam finalwip
        $tab_delsched_final = DB::table('delsched_final')
            ->where('status', '=', 'danger')
            ->orWhere('status', '=', 'light')
            ->orderBy('id', 'asc')
            ->get();

        foreach ($tab_delsched_final as $delsched_final) {
            $val_id = $delsched_final->id;
            $val_delivery_date = $delsched_final->delivery_date;
            $val_so_number = $delsched_final->so_number;
            $val_customer_code = $delsched_final->customer_code;
            $val_customer_name = $delsched_final->customer_name;
            $val_item_code = $delsched_final->item_code;
            $val_item_name = $delsched_final->item_name;
            $val_outstanding_stk = $delsched_final->outstanding_stk;

            $tab_sap_bom_wip_check = DB::table('sap_bom_wip')
                ->where('fg_code', '=', $val_item_code)
                ->first();

            if (empty($tab_sap_bom_wip_check->fg_code)) {
            } else {
                $tab_sap_bom_wip = DB::table('sap_bom_wip')
                    ->where('fg_code', '=', $val_item_code)
                    ->get();

                foreach ($tab_sap_bom_wip as $sap_bom_wip) {
                    $val_semi_first = $sap_bom_wip->semi_first;
                    $val_semi_second = $sap_bom_wip->semi_second;
                    $val_semi_third = $sap_bom_wip->semi_third;
                    $val_bom_qty_first = $sap_bom_wip->qty_first;
                    $val_bom_qty_second = $sap_bom_wip->qty_second;
                    $val_bom_qty_third = $sap_bom_wip->qty_third;
                    $val_level = $sap_bom_wip->level;

                    if ($val_level == 3) {
                        $rcd_bom_qty =
                            $val_bom_qty_first * $val_bom_qty_second * $val_bom_qty_third;
                        $cal_req_qty = $rcd_bom_qty * $val_outstanding_stk;
                        $rcd_wip = $val_semi_third;
                    } elseif ($val_level == 2) {
                        $rcd_bom_qty = $val_bom_qty_first * $val_bom_qty_second;
                        $cal_req_qty = $rcd_bom_qty * $val_outstanding_stk;
                        $rcd_wip = $val_semi_second;
                    } else {
                        $rcd_bom_qty = $val_bom_qty_first;
                        $cal_req_qty = $rcd_bom_qty * $val_outstanding_stk;
                        $rcd_wip = $val_semi_first;
                    }

                    $tab_sap_inventory_fg = DB::table('sap_inventory_fg')
                        ->where('item_code', '=', $rcd_wip)
                        ->first();

                    $val_wip_name = $tab_sap_inventory_fg->item_name;
                    $val_stock = $tab_sap_inventory_fg->stock;
                    $val_process_owner = $tab_sap_inventory_fg->process_owner;
                    if ($val_process_owner == 'INJ') {
                        $val_departement = 390;
                    } elseif ($val_process_owner == 'SEC') {
                        $val_departement = 361;
                    } else {
                        $val_departement = 362;
                    }

                    $ins_finalwip = [
                        'fglink_id' => $val_id,
                        'delivery_date' => $val_delivery_date,
                        'so_number' => $val_so_number,
                        'customer_code' => $val_customer_code,
                        'customer_name' => $val_customer_name,
                        'item_code' => $val_item_code,
                        'item_name' => $val_item_name,
                        'outstanding_del' => $val_outstanding_stk,
                        'wip_code' => $rcd_wip,
                        'wip_name' => $val_wip_name,
                        'departement' => $val_departement,
                        'bom_level' => $val_level,
                        'bom_quantity' => $rcd_bom_qty,
                        'req_quantity' => $cal_req_qty,
                        'stock_wip' => $val_stock,
                        'balance_wip' => $val_stock,
                        'status' => 'light',
                    ];
                    DelschedFinalWip::insert($ins_finalwip);
                }

                /*
                $ins_finalwip = array('delivery_date' => $val_delivery_date, 'so_number' => $val_so_number, 'customer_code' => $val_customer_code,
                'customer_name' => $val_customer_name, 'item_code' => $val_item_code, 'item_name' => $val_item_name, 'outstanding_del' => $val_outstanding_stk);
                delsched_finalwip::insert($ins_finalwip);
                */
            }
        }

        return redirect()->route('delschedwip.step2');
    }

    public function step2wip()
    {
        // 2. Filter delsched_finalwip dengan penarikan dari stok
        $tab_delsched_finalwip_item = DB::table('delsched_finalwip')
            ->select('wip_code')
            ->distinct()
            ->get();

        foreach ($tab_delsched_finalwip_item as $delsched_finalwip_item) {
            $val_wip_code = $delsched_finalwip_item->wip_code;

            $tab_sap_inventoryfg = DB::table('sap_inventory_fg')
                ->where('item_code', '=', $val_wip_code)
                ->first();
            $val_stock = $tab_sap_inventoryfg->stock;

            $ins_stockwip = [
                'item_code' => $val_wip_code,
                'quantity' => $val_stock,
                'total_after' => $val_stock,
            ];
            delsched_stockwip::insert($ins_stockwip);
        }

        $tab_delsched_finalwip = DB::table('delsched_finalwip')->orderBy('id', 'asc')->get();

        foreach ($tab_delsched_finalwip as $delsched_finalwip) {
            $date_now = Carbon::now();

            $val_finalwip_id = $delsched_finalwip->id;
            $val_wip_code_finalwip = $delsched_finalwip->wip_code;
            $val_delivery_date = $delsched_finalwip->delivery_date;
            $val_req_quantity = $delsched_finalwip->req_quantity;
            $val_outstanding_wip = $delsched_finalwip->outstanding_wip;

            $tab_delsched_stockwip = DB::table('delsched_stockwip')
                ->where('item_code', '=', $val_wip_code_finalwip)
                ->first();
            $val_stockwip_id = $tab_delsched_stockwip->id;
            $val_stockwip_new = $tab_delsched_stockwip->quantity;
            $val_total_after = $tab_delsched_stockwip->total_after;

            if ($val_total_after < 0) {
                $cal_outstanding_wip_new = $val_req_quantity;
                $cal_total_after_now = $val_total_after - $val_req_quantity;

                if ($val_delivery_date <= $date_now) {
                    $rcd_status = 'danger';
                } else {
                    $rcd_status = 'light';
                }
            } else {
                if ($val_total_after >= $val_req_quantity) {
                    $cal_outstanding_wip_new = 0;
                    $cal_total_after_now = $val_total_after - $val_req_quantity;

                    if ($val_delivery_date <= $date_now) {
                        $rcd_status = 'danger';
                    } else {
                        $rcd_status = 'light';
                    }
                } else {
                    $cal_outstanding_wip_new = $val_req_quantity - $val_total_after;
                    $cal_total_after_now = $val_total_after - $val_req_quantity;

                    if ($val_delivery_date <= $date_now) {
                        $rcd_status = 'danger';
                    } else {
                        $rcd_status = 'light';
                    }
                }
            }

            $update_stock = DB::table('delsched_stockwip')
                ->where('id', $val_stockwip_id)
                ->update([
                    'total_after' => $cal_total_after_now,
                ]);

            $update_final = DB::table('delsched_finalwip')
                ->where('id', $val_finalwip_id)
                ->update([
                    'stock_wip' => $val_stockwip_new,
                    'balance_wip' => $cal_total_after_now,
                    'outstanding_wip' => $cal_outstanding_wip_new,
                    'status' => $rcd_status,
                ]);
        }

        $now = new DateTime;
        $now->modify('+420 minutes');

        $tb_datelist = DB::table('uti_date_list')
            ->where('id', '14')
            ->update([
                'updated_at' => $now,
            ]);

        $this->statusFinish();

        return redirect()->route('indexfinalwip');
    }

    public function statusFinish()
    {
        // Retrieve all records from the delsched_final table
        $datas = delsched_final::get();

        $datas2 = delsched_finalwip::get();

        $today = Carbon::today();
        //  dd($today);

        foreach ($datas as $data) {
            // Check if doc_status is 'C' or if delivery_qty is equal to delivered
            if ($data->doc_status === 'C' || $data->delivery_qty === $data->delivered) {
                // Update the status to 'Finish' if the doc_status is 'C' or if delivery_qty equals delivered
                $data->status = 'Finish';
            } elseif (
                $data->doc_status === 'O' &&
                $today->diffInDays($data->delivery_date, false) == 2
            ) {
                // Update the status to 'Danger' if the doc_status is 'O' and the delivery_date is 2 days from today
                $data->status = 'Danger';
            } elseif (
                $data->doc_status === 'O' &&
                $today->diffInDays($data->delivery_date, false) == -2
            ) {
                // Update the status to 'Warning' if the doc_status is 'O' and the delivery_date was 2 days ago
                $data->status = 'Warning';
            } else {
                // For all other conditions, set the status to 'Warning'
                $data->status = 'Warning';
            }

            $data->save();
        }

        foreach ($datas2 as $dataw) {
            // Check if doc_status is 'C' or if delivery_qty is equal to delivered

            if ($dataw->balance_wip < 0) {
                // Update the status to 'Finish'
                $dataw->status = 'Warning';
            }

            if ($dataw->balance_wip > 0) {
                // Update the status to 'Finish'
                $dataw->status = 'Finish';
            }

            if ($dataw->balance_wip < 0 && $today->diffInDays($dataw->delivery_date, false) == 5) {
                // Update the status to 'Danger'
                $dataw->status = 'Danger';
            }

            //    if($today->diffInDays($dataw->delivery_date, false) == -5) {
            // 	   // Update the status to 'Warning'
            // 	   $dataw->status = 'Warning';
            //    }

            $dataw->save();
        }

        // Output the updated data for verification
        return redirect()->route('indexfinalwip');
    }
}
