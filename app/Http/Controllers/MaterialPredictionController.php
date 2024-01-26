<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use App\Models\foremindFinal;
use App\Models\ForecastMaterialPrediction;
use App\Models\sapFctinventoryFg;
use Carbon\Carbon;


class materialPredictionController extends Controller
{

    public function processForemindFinalData()
    {

       // Retrieve forecasts from the foremindFinal table
                $forecasts = ForemindFinal::all();
                $transformedData = [];

                        // Get unique months from all forecasts
                $allMonths = [];

                foreach ($forecasts as $forecast) {
                    $dayForecast = Carbon::parse($forecast->day_forecast);
                    $allMonths[] = $dayForecast->format('Y-m');
                }

                // Code untuk ambil jumlah bulan yang ada di database
                $uniqueMonths = array_unique($allMonths);
                sort($uniqueMonths);



                foreach ($forecasts as $forecast) {
                    // Extract necessary data
                    $materialCode = $forecast->material_code;
                    $materialName = $forecast->material_name;
                    $customer = $forecast->forecast_name;
                    $itemNo = $forecast->Item_no;
                    $unitOfMeasure = $forecast->{'U/M'};
                    $materialquantity = (double) $forecast->quantity_material;
                    $vendorName = $forecast->vendor_name;
                    $vendorCode = $forecast->vendor_code;
                   
                    

                    // Ensure that the necessary keys are initialized
                    if (!isset($transformedData[$materialCode])) {
                        $transformedData[$materialCode] = [];
                    }
                
                    if (!isset($transformedData[$materialCode][$materialName])) {
                        $transformedData[$materialCode][$materialName] = [];
                    }
                    
                    if (!isset($transformedData[$materialCode][$materialName][$customer])) {
                        $transformedData[$materialCode][$materialName][$customer] = [];
                    }

                    if (!isset($transformedData[$materialCode][$materialName][$customer][$itemNo])) {
                        $transformedData[$materialCode][$materialName][$customer][$itemNo] = [];
                    }
                
                    if (!isset($transformedData[$materialCode][$materialName][$customer][$itemNo][$unitOfMeasure])) {
                        $transformedData[$materialCode][$materialName][$customer][$itemNo][$unitOfMeasure] = [];
                    }

                    // Use string representation of the quantity as the key
                    $quantityKey = (string) $materialquantity;

                    if (!isset($transformedData[$materialCode][$materialName][$customer][$itemNo][$unitOfMeasure][$quantityKey])) {
                        $transformedData[$materialCode][$materialName][$customer][$itemNo][$unitOfMeasure][$quantityKey] = [];
                    }

                    if (!isset($transformedData[$materialCode][$materialName][$customer][$itemNo][$unitOfMeasure][$quantityKey][$vendorCode])) {
                        $transformedData[$materialCode][$materialName][$customer][$itemNo][$unitOfMeasure][$quantityKey][$vendorCode] = [];
                    }
                
                
                    if (!isset($transformedData[$materialCode][$materialName][$customer][$itemNo][$unitOfMeasure][$quantityKey][$vendorCode][$vendorName])) {
                        $transformedData[$materialCode][$materialName][$customer][$itemNo][$unitOfMeasure][$quantityKey][$vendorCode][$vendorName] = ['months' => [],
                        'quantity_forecast' => [],];
                    }
                
                    // Convert 'day_forecast' to Carbon instance
                    $dayForecast = Carbon::parse($forecast->day_forecast);
                    $monthYear = $dayForecast->format('Y-m');
                
                    // Ensure that the necessary keys are initialized for months
                    foreach ($uniqueMonths as $uniqueMonth) {
                        if (!isset($transformedData[$materialCode][$materialName][$customer][$itemNo][$unitOfMeasure][$quantityKey][$vendorCode][$vendorName]['months'][$uniqueMonth])) {
                            $transformedData[$materialCode][$materialName][$customer][$itemNo][$unitOfMeasure][$quantityKey][$vendorCode][$vendorName]['months'][$uniqueMonth] = 0;
                            $transformedData[$materialCode][$materialName][$customer][$itemNo][$unitOfMeasure][$quantityKey][$vendorCode][$vendorName]['quantity_forecast'][$uniqueMonth] = 0;
                        }
                    }
                
                    // Increment the value based on material_prediction
                    $transformedData[$materialCode][$materialName][$customer][$itemNo][$unitOfMeasure][$quantityKey][$vendorCode][$vendorName]['months'][$monthYear] += $forecast->material_prediction;
                   
                    // Store quantity_forecast
                    $transformedData[$materialCode][$materialName][$customer][$itemNo][$unitOfMeasure][$quantityKey][$vendorCode][$vendorName]['quantity_forecast'][$monthYear] = $forecast->quantity_forecast;
                   
                }
               
                                // Loop through transformed data to store it in the forecast_material_prediction table
                foreach ($transformedData as $materialCode => $materialData) {
                    foreach ($materialData as $materialName => $unitData) {
                        foreach($unitData as $customer => $nextdata){
                            foreach($nextdata as $itemNo => $unitDatas){
                                foreach ($unitDatas as $unitOfMeasure => $UnitM) {
                                    foreach($UnitM as $quantityKey =>$vendorData){
                                        foreach ($vendorData as $vendorCode => $dataVendor) {
                                            foreach ($dataVendor as $vendorName => $data) {
                                        
                                                    ForecastMaterialPrediction::create([
                                                        'material_code' => $materialCode,
                                                        'material_name' => $materialName,
                                                        'customer' => $customer,
                                                        'item_no' => $itemNo,
                                                        'unit_of_measure' => $unitOfMeasure,
                                                        'quantity_material' => $quantityKey,
                                                        'vendor_code' => $vendorCode,
                                                        'vendor_name' => $vendorName,
                                                        'months' => json_encode($data['months'] + [$monthYear => 0]),
                                                        'quantity_forecast' => json_encode($data['quantity_forecast']),
                                                    ]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
    }

  
}
