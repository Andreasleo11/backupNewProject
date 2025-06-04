<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 100000);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use App\Models\sapForecast;
use App\Models\sapFctBomWip;
use App\Models\sapFctInventoryMtr;
use App\Models\foremindFinal;
use App\Models\sapFctBomWipFirst;
use App\Models\sapFctBomWipSecond;
use App\Models\sapFctBomWipThird;
use App\Models\sapFctBomWipFgCode;
use App\Models\PurchasingUpdateLog;
use Carbon\Carbon;


class PurchasingMaterialController extends Controller
{

    public function storeDataInNewTable()
    {
        $log = PurchasingUpdateLog::find(1);
        $log->updated_at = Carbon::now();
        $log->save();
        // function buat insert data
        $case1Data = SapForecast::whereNotIn('item_no', function ($query) {
            $query->select('fg_code')->from('sap_fct_bom_wip');
        })->with('inventoryMtr')->get();


        $case2Data = SapForecast::whereHas('bomWip', function ($query) {
            $query->whereHas('rawMaterialFgcode');
        })->with('bomWip.rawMaterialFgcode')->get();
        // dd($case2Data);

        $case3Data = SapForecast::whereHas('firstBomWip', function ($query) {
            $query->where('level', 1)->whereHas('semiFirstInventoryMtrForecast');
        })
            ->with('firstBomWip.semiFirstInventoryMtrForecast')->get();

        $case4Data = SapForecast::whereHas('secondBomWip', function ($query) {
            $query->where('level', 2)->whereHas('semiSecondInventoryMtrForecast');
        })->with('secondBomWip.semiSecondInventoryMtrForecast')->get();

        $case5Data = SapForecast::whereHas('thirdBomWip', function ($query) {
            $query->where('level', 3)->whereHas('semiThirdInventoryMtrForecast');
        })->with('thirdBomWip.semiThirdInventoryMtrForecast')->get();


        // Iterate through each dataset and store in the new_table
        $this->Insert_final($case1Data);
        $this->Insert_finalrest($case2Data);
        $this->Insert_finalrest1($case3Data);
        $this->Insert_finalrest2($case4Data);
        $this->Insert_finalrest3($case5Data);
    }

    private function Insert_final($data) // DONE
    {

        foreach ($data as $item) {

            $vendor_code = null;
            $vendor_name = null;
            $material_code = null;
            $material_name = null;
            $item_group = null;
            $material_quan = null;
            $materialPrediction = null;
            $material_measure = null;

            $inventoryMtra = $item->inventoryMtr;
            foreach ($inventoryMtra as $inventoryMtrData) {
                $vendor_code = $inventoryMtrData->vendor_code;
                $vendor_name = $inventoryMtrData->vendor_name;
                $material_code = $inventoryMtrData->material_code;
                $material_name = $inventoryMtrData->material_name;
                $item_group = $inventoryMtrData->item_group;
                $material_quan = $inventoryMtrData->material_quantity;
                $material_measure = $inventoryMtrData->Measure;
                $materialPrediction = $material_quan * $item->quantity;

                foremindFinal::create([
                    'forecast_code' => $item->forecast_code,
                    'forecast_name' => $item->forecast_name,
                    'vendor_code' => $vendor_code,
                    'vendor_name' => $vendor_name,
                    'day_forecast' => $item->forecast_date,
                    'Item_no' => $item->item_no,
                    'quantity_forecast' => $item->quantity,
                    'item_group' => $item_group,
                    'material_code' => $material_code,
                    'material_name' => $material_name,
                    'quantity_material' => $material_quan,
                    'material_prediction' => $materialPrediction,
                    'U/M' => $material_measure

                ]);
            }
        }
    }

    private function Insert_finalrest($data) // DONE
    {

        foreach ($data as $item) {

            $vendor_code = null;
            $vendor_name = null;
            $material_code = null;
            $material_name = null;
            $item_group = null;
            $material_quan = null;
            $materialPrediction = null;
            $material_measure = null;
            // dd($item);
            $bomWipI = $item->bomWip;
            foreach ($bomWipI as $bomWipItem) {
                $inventoryQu = $bomWipItem->rawMaterialFgcode;

                foreach ($inventoryQu as $inventoryQuantity) {
                    $vendor_code = $inventoryQuantity->vendor_code;
                    $vendor_name = $inventoryQuantity->vendor_name;
                    $material_code = $inventoryQuantity->material_code;
                    $material_name = $inventoryQuantity->material_name;
                    $item_group = $inventoryQuantity->item_group;
                    $material_quan = $inventoryQuantity->material_quantity;
                    $material_measure = $inventoryQuantity->Measure;
                    $materialPrediction = $material_quan * $item->quantity;

                    foremindFinal::create([
                        'forecast_code' => $item->forecast_code,
                        'forecast_name' => $item->forecast_name,
                        'vendor_code' => $vendor_code,
                        'vendor_name' => $vendor_name,
                        'day_forecast' => $item->forecast_date,
                        'Item_no' => $item->item_no,
                        'quantity_forecast' => $item->quantity,
                        'item_group' => $item_group,
                        'material_code' => $material_code,
                        'material_name' => $material_name,
                        'quantity_material' => $material_quan,
                        'material_prediction' => $materialPrediction,
                        'U/M' => $material_measure
                    ]);
                }
            }
        }
    }



    private function Insert_finalrest1($data) // DONE
    {

        foreach ($data as $item) {

            $vendor_code = null;
            $vendor_name = null;
            $material_code = null;
            $material_name = null;
            $item_group = null;
            $material_quan = null;
            $materialPrediction = null;
            $bom_quantity = null;
            $material_measure = null;
            $semi_code = null;
            $bomWipI = $item->firstBomWip;

            foreach ($bomWipI as $bomWipItem) {
                $bom_quantity = $bomWipItem->bom_quantity;
                $semi_code = $bomWipItem->semi_first;
                $inventoryQu = $bomWipItem->semiFirstInventoryMtrForecast;

                foreach ($inventoryQu as $inventoryQuantity) {
                    $vendor_code = $inventoryQuantity->vendor_code;
                    $vendor_name = $inventoryQuantity->vendor_name;
                    $material_code = $inventoryQuantity->material_code;
                    $material_name = $inventoryQuantity->material_name;
                    $item_group = $inventoryQuantity->item_group;
                    $material_quan = $inventoryQuantity->material_quantity * $bom_quantity;
                    $material_measure = $inventoryQuantity->Measure;
                    $materialPrediction = $material_quan * $item->quantity;

                    foremindFinal::create([
                        'forecast_code' => $item->forecast_code,
                        'forecast_name' => $item->forecast_name,
                        'vendor_code' => $vendor_code,
                        'vendor_name' => $vendor_name,
                        'day_forecast' => $item->forecast_date,
                        'Item_no' => $item->item_no,
                        'semi_code' => $semi_code,
                        'quantity_forecast' => $item->quantity,
                        'item_group' => $item_group,
                        'material_code' => $material_code,
                        'material_name' => $material_name,
                        'quantity_material' => $material_quan,
                        'quantity_bomWip' => $bom_quantity,
                        'material_prediction' => $materialPrediction,
                        'U/M' => $material_measure
                    ]);
                }
            }
        }
    }

    private function Insert_finalrest2($data) // DONE
    {

        foreach ($data as $item) {

            $vendor_code = null;
            $vendor_name = null;
            $material_code = null;
            $material_name = null;
            $item_group = null;
            $material_quan = null;
            $materialPrediction = null;
            $bom_quantity = null;
            $material_measure = null;
            $semi_code = null;

            $bomWipItem = $item->secondBomWip;

            foreach ($bomWipItem as $bomWipItems) {
                $bom_quantity = $bomWipItems->bom_quantity;
                $semi_code = $bomWipItems->semi_second;

                $inventoryQuantity = $bomWipItems->semiSecondInventoryMtrForecast;
                foreach ($inventoryQuantity as $secondInventory) {
                    $vendor_code = $secondInventory->vendor_code;
                    $vendor_name = $secondInventory->vendor_name;
                    $material_code = $secondInventory->material_code;
                    $material_name = $secondInventory->material_name;
                    $item_group = $secondInventory->item_group;
                    $material_quan = $secondInventory->material_quantity * $bom_quantity;
                    $material_measure = $secondInventory->Measure;
                    $materialPrediction = $material_quan * $item->quantity;

                    foremindFinal::create([
                        'forecast_code' => $item->forecast_code,
                        'forecast_name' => $item->forecast_name,
                        'vendor_code' => $vendor_code,
                        'vendor_name' => $vendor_name,
                        'day_forecast' => $item->forecast_date,
                        'Item_no' => $item->item_no,
                        'semi_code' => $semi_code,
                        'quantity_forecast' => $item->quantity,
                        'item_group' => $item_group,
                        'material_code' => $material_code,
                        'material_name' => $material_name,
                        'quantity_material' => $material_quan,
                        'quantity_bomWip' => $bom_quantity,
                        'material_prediction' => $materialPrediction,
                        'U/M' => $material_measure
                    ]);
                }
            }
        }
    }


    private function Insert_finalrest3($data) // DONE
    {
        // dd($data->toArray());
        foreach ($data as $item) {

            $vendor_code = null;
            $vendor_name = null;
            $material_code = null;
            $material_name = null;
            $item_group = null;
            $material_quan = null;
            $materialPrediction = null;
            $bom_quantity = null;
            $material_measure = null;
            $semi_code = null;

            $bomWipItem = $item->thirdBomWip;
            // dd($bomWipItem);
            foreach ($bomWipItem as $bomWipItems) {
                $bom_quantity = $bomWipItems->bom_quantity;
                $semi_code = $bomWipItems->semi_third;
                $inventoryQuantity = $bomWipItems->semiThirdInventoryMtrForecast;
                foreach ($inventoryQuantity as $thirdInventory) {
                    $vendor_code = $thirdInventory->vendor_code;
                    $vendor_name = $thirdInventory->vendor_name;
                    $material_code = $thirdInventory->material_code;
                    $material_name = $thirdInventory->material_name;
                    $item_group = $thirdInventory->item_group;
                    $material_quan = $thirdInventory->material_quantity * $bom_quantity;
                    $material_measure = $thirdInventory->Measure;
                    $materialPrediction = $material_quan * $item->quantity;
                    foremindFinal::create([
                        'forecast_code' => $item->forecast_code,
                        'forecast_name' => $item->forecast_name,
                        'vendor_code' => $vendor_code,
                        'vendor_name' => $vendor_name,
                        'day_forecast' => $item->forecast_date,
                        'Item_no' => $item->item_no,
                        'semi_code' => $semi_code,
                        'quantity_forecast' => $item->quantity,
                        'item_group' => $item_group,
                        'material_code' => $material_code,
                        'material_name' => $material_name,
                        'quantity_material' => $material_quan,
                        'quantity_bomWip' => $bom_quantity,
                        'material_prediction' => $materialPrediction,
                        'U/M' => $material_measure
                        // Add more attributes as needed
                    ]);
                }
            }

            //     $existingRecord = foremindFinal::where([
            //         'forecast_code' => $item->forecast_code,
            //         'forecast_name' => $item->forecast_name,
            //         'vendor_code' => $vendor_code,
            //         'vendor_name' => $vendor_name,
            //         'day_forecast' => $item->forecast_date,
            //         'Item_no' => $item->item_no,
            //         'quantity_forecast' => $item->quantity,
            //         'item_group' => $item_group,
            //         'material_code' => $material_code,
            //         'material_name' => $material_name,
            //         'quantity_material' => $material_quan,
            //         'quantity_bomWip' => $bom_quantity,
            //         'material_prediction' => $materialPrediction,
            //         'U/M' => $material_measure
            //         // Add more unique fields as needed
            //     ])->first();

            //     if (!$existingRecord)
            // {

            // }
        }
    }
}
