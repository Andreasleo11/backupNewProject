<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 100000);

use App\Models\foremindFinal;
use App\Models\PurchasingUpdateLog;
use App\Models\sapForecast;
use Carbon\Carbon;

class PurchasingMaterialController extends Controller
{
    public function storeDataInNewTable()
    {
        \Illuminate\Support\Facades\DB::disableQueryLog();
        $log = PurchasingUpdateLog::find(1);
        $log->updated_at = Carbon::now();
        $log->save();
        // function buat insert data
        $case1Data = SapForecast::whereNotIn('item_no', function ($query) {
            $query->select('fg_code')->from('sap_fct_bom_wip');
        })
            ->with('inventoryMtr')
            ->get();

        $case2Data = SapForecast::whereHas('bomWip', function ($query) {
            $query->whereHas('rawMaterialFgcode');
        })
            ->with('bomWip.rawMaterialFgcode')
            ->get();
        // dd($case2Data);

        $case3Data = SapForecast::whereHas('firstBomWip', function ($query) {
            $query->where('level', 1)->whereHas('semiFirstInventoryMtrForecast');
        })
            ->with('firstBomWip.semiFirstInventoryMtrForecast')
            ->get();

        $case4Data = SapForecast::whereHas('secondBomWip', function ($query) {
            $query->where('level', 2)->whereHas('semiSecondInventoryMtrForecast');
        })
            ->with('secondBomWip.semiSecondInventoryMtrForecast')
            ->get();

        $case5Data = SapForecast::whereHas('thirdBomWip', function ($query) {
            $query->where('level', 3)->whereHas('semiThirdInventoryMtrForecast');
        })
            ->with('thirdBomWip.semiThirdInventoryMtrForecast')
            ->get();

        // Iterate through each dataset and store in the new_table
        $this->Insert_final($case1Data);
        $this->Insert_finalrest($case2Data);
        $this->Insert_finalrest1($case3Data);
        $this->Insert_finalrest2($case4Data);
        $this->Insert_finalrest3($case5Data);
    }

    private function Insert_final($data)
    {
        $inserts = [];
        foreach ($data as $item) {
            $inventoryMtra = $item->inventoryMtr;
            foreach ($inventoryMtra as $inventoryMtrData) {
                $inserts[] = [
                    'forecast_code' => $item->forecast_code,
                    'forecast_name' => $item->forecast_name,
                    'vendor_code' => $inventoryMtrData->vendor_code,
                    'vendor_name' => $inventoryMtrData->vendor_name,
                    'day_forecast' => $item->forecast_date,
                    'Item_no' => $item->item_no,
                    'quantity_forecast' => $item->quantity,
                    'item_group' => $inventoryMtrData->item_group,
                    'material_code' => $inventoryMtrData->material_code,
                    'material_name' => $inventoryMtrData->material_name,
                    'quantity_material' => $inventoryMtrData->material_quantity,
                    'material_prediction' => $inventoryMtrData->material_quantity * $item->quantity,
                    'U/M' => $inventoryMtrData->Measure,
                ];

                if (count($inserts) >= 500) {
                    \Illuminate\Support\Facades\DB::table('foremind_final')->insert($inserts);
                    $inserts = [];
                }
            }
        }
        if (count($inserts) > 0) {
            \Illuminate\Support\Facades\DB::table('foremind_final')->insert($inserts);
        }
    }

    private function Insert_finalrest($data)
    {
        $inserts = [];
        foreach ($data as $item) {
            $bomWipI = $item->bomWip;
            foreach ($bomWipI as $bomWipItem) {
                $inventoryQu = $bomWipItem->rawMaterialFgcode;

                foreach ($inventoryQu as $inventoryQuantity) {
                    $inserts[] = [
                        'forecast_code' => $item->forecast_code,
                        'forecast_name' => $item->forecast_name,
                        'vendor_code' => $inventoryQuantity->vendor_code,
                        'vendor_name' => $inventoryQuantity->vendor_name,
                        'day_forecast' => $item->forecast_date,
                        'Item_no' => $item->item_no,
                        'quantity_forecast' => $item->quantity,
                        'item_group' => $inventoryQuantity->item_group,
                        'material_code' => $inventoryQuantity->material_code,
                        'material_name' => $inventoryQuantity->material_name,
                        'quantity_material' => $inventoryQuantity->material_quantity,
                        'material_prediction' => $inventoryQuantity->material_quantity * $item->quantity,
                        'U/M' => $inventoryQuantity->Measure,
                    ];

                    if (count($inserts) >= 500) {
                        \Illuminate\Support\Facades\DB::table('foremind_final')->insert($inserts);
                        $inserts = [];
                    }
                }
            }
        }
        if (count($inserts) > 0) {
            \Illuminate\Support\Facades\DB::table('foremind_final')->insert($inserts);
        }
    }

    private function Insert_finalrest1($data)
    {
        $inserts = [];
        foreach ($data as $item) {
            $bomWipI = $item->firstBomWip;

            foreach ($bomWipI as $bomWipItem) {
                $bom_quantity = $bomWipItem->bom_quantity;
                $semi_code = $bomWipItem->semi_first;
                $inventoryQu = $bomWipItem->semiFirstInventoryMtrForecast;

                foreach ($inventoryQu as $inventoryQuantity) {
                    $inserts[] = [
                        'forecast_code' => $item->forecast_code,
                        'forecast_name' => $item->forecast_name,
                        'vendor_code' => $inventoryQuantity->vendor_code,
                        'vendor_name' => $inventoryQuantity->vendor_name,
                        'day_forecast' => $item->forecast_date,
                        'Item_no' => $item->item_no,
                        'semi_code' => $semi_code,
                        'quantity_forecast' => $item->quantity,
                        'item_group' => $inventoryQuantity->item_group,
                        'material_code' => $inventoryQuantity->material_code,
                        'material_name' => $inventoryQuantity->material_name,
                        'quantity_material' => $inventoryQuantity->material_quantity * $bom_quantity,
                        'quantity_bomWip' => $bom_quantity,
                        'material_prediction' => $inventoryQuantity->material_quantity * $bom_quantity * $item->quantity,
                        'U/M' => $inventoryQuantity->Measure,
                    ];

                    if (count($inserts) >= 500) {
                        \Illuminate\Support\Facades\DB::table('foremind_final')->insert($inserts);
                        $inserts = [];
                    }
                }
            }
        }
        if (count($inserts) > 0) {
            \Illuminate\Support\Facades\DB::table('foremind_final')->insert($inserts);
        }
    }

    private function Insert_finalrest2($data)
    {
        $inserts = [];
        foreach ($data as $item) {
            $bomWipItem = $item->secondBomWip;

            foreach ($bomWipItem as $bomWipItems) {
                $bom_quantity = $bomWipItems->bom_quantity;
                $semi_code = $bomWipItems->semi_second;

                $inventoryQuantity = $bomWipItems->semiSecondInventoryMtrForecast;
                foreach ($inventoryQuantity as $secondInventory) {
                    $inserts[] = [
                        'forecast_code' => $item->forecast_code,
                        'forecast_name' => $item->forecast_name,
                        'vendor_code' => $secondInventory->vendor_code,
                        'vendor_name' => $secondInventory->vendor_name,
                        'day_forecast' => $item->forecast_date,
                        'Item_no' => $item->item_no,
                        'semi_code' => $semi_code,
                        'quantity_forecast' => $item->quantity,
                        'item_group' => $secondInventory->item_group,
                        'material_code' => $secondInventory->material_code,
                        'material_name' => $secondInventory->material_name,
                        'quantity_material' => $secondInventory->material_quantity * $bom_quantity,
                        'quantity_bomWip' => $bom_quantity,
                        'material_prediction' => $secondInventory->material_quantity * $bom_quantity * $item->quantity,
                        'U/M' => $secondInventory->Measure,
                    ];

                    if (count($inserts) >= 500) {
                        \Illuminate\Support\Facades\DB::table('foremind_final')->insert($inserts);
                        $inserts = [];
                    }
                }
            }
        }
        if (count($inserts) > 0) {
            \Illuminate\Support\Facades\DB::table('foremind_final')->insert($inserts);
        }
    }

    private function Insert_finalrest3($data)
    {
        $inserts = [];
        foreach ($data as $item) {
            $bomWipItem = $item->thirdBomWip;
            foreach ($bomWipItem as $bomWipItems) {
                $bom_quantity = $bomWipItems->bom_quantity;
                $semi_code = $bomWipItems->semi_third;
                $inventoryQuantity = $bomWipItems->semiThirdInventoryMtrForecast;
                foreach ($inventoryQuantity as $thirdInventory) {
                    $inserts[] = [
                        'forecast_code' => $item->forecast_code,
                        'forecast_name' => $item->forecast_name,
                        'vendor_code' => $thirdInventory->vendor_code,
                        'vendor_name' => $thirdInventory->vendor_name,
                        'day_forecast' => $item->forecast_date,
                        'Item_no' => $item->item_no,
                        'semi_code' => $semi_code,
                        'quantity_forecast' => $item->quantity,
                        'item_group' => $thirdInventory->item_group,
                        'material_code' => $thirdInventory->material_code,
                        'material_name' => $thirdInventory->material_name,
                        'quantity_material' => $thirdInventory->material_quantity * $bom_quantity,
                        'quantity_bomWip' => $bom_quantity,
                        'material_prediction' => $thirdInventory->material_quantity * $bom_quantity * $item->quantity,
                        'U/M' => $thirdInventory->Measure,
                    ];

                    if (count($inserts) >= 500) {
                        \Illuminate\Support\Facades\DB::table('foremind_final')->insert($inserts);
                        $inserts = [];
                    }
                }
            }
        }
        if (count($inserts) > 0) {
            \Illuminate\Support\Facades\DB::table('foremind_final')->insert($inserts);
        }
    }
}
