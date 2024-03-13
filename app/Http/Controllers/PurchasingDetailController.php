<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\foremindFinal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use App\Exports\ForExport;
use App\Exports\ForExportCustomer;
use Maatwebsite\Excel\Facades\Excel;

class PurchasingDetailController extends Controller
{
    public function index(Request $request)
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

            // Ensure unique months and sort them
            $uniqueMonths = array_unique($allMonths);
            sort($uniqueMonths);

            // Get vendor code from user input
            $vendorCode = $request->input('vendor_code');

            // Fetch your materials data from the database based on vendor code
            if ($vendorCode) {
                $materials = DB::table('forecast_material_predictions')
                    ->where('vendor_code', $vendorCode)
                    ->get();
            } else {
                $materials = []; // Empty array if no vendor code provided
            }




            $allmonth = [];
            foreach($materials as $material){
                $decodedMonths = json_decode($material->months, true);
                $stringMonths = json_decode($decodedMonths, true);
                $decodedForecast = json_decode($material->quantity_forecast, true);
                // dd($decodedForecast);
                $stringForecast = json_decode($decodedForecast, true);
                // dd($stringForecast);
                $truevalue[] = $stringMonths;

                //  if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedMonths)) {
                //     // Handle JSON decoding error
                //     dd('JSON Decoding Error:', json_last_error_msg());
                // }
                $decodedForecast = json_decode($material->quantity_forecast, true);
            // dd($decodedForecast);
            $stringForecast = json_decode($decodedForecast, true);
            // dd($stringForecast);
                $monthm[] = array_keys($stringMonths);
                $values[] = array_values($stringMonths);
                $qforecast[] = array_values($stringForecast);

            }

            $vendorCode = $request->input('vendor_code');

            return view('purchasing.foremind_detail_print',  [ // sedang bikin customer (report 1 nya)
                'monthm' => $monthm, // Ensure this is the correct data
                'materials' => $materials,
                'values' => $values,
                'mon' => $uniqueMonths,
                'vendorCode' => $vendorCode,
                'qforecast' => $qforecast,
                ])->render();
    }

    public function indexCustomer(Request $request)
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

            // Ensure unique months and sort them
            $uniqueMonths = array_unique($allMonths);
            sort($uniqueMonths);

            // Get vendor code from user input
            $vendorCode = $request->input('vendor_code');

            // Fetch your materials data from the database based on vendor code
            if ($vendorCode) {
                $materials = DB::table('forecast_material_predictions')
                    ->where('vendor_code', $vendorCode)
                    ->get();
            } else {
                $materials = []; // Empty array if no vendor code provided
            }





        $allmonth = [];
        foreach($materials as $material){
                $decodedMonths = json_decode($material->months, true);
                $stringMonths = json_decode($decodedMonths, true);
                $decodedForecast = json_decode($material->quantity_forecast, true);
                // dd($decodedForecast);
                $stringForecast = json_decode($decodedForecast, true);
                // dd($stringForecast);
                $truevalue[] = $stringMonths;

                //  if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedMonths)) {
                //     // Handle JSON decoding error
                //     dd('JSON Decoding Error:', json_last_error_msg());
                // }
                $decodedForecast = json_decode($material->quantity_forecast, true);
            // dd($decodedForecast);
            $stringForecast = json_decode($decodedForecast, true);
            // dd($stringForecast);
                $monthm[] = array_keys($stringMonths);
                $values[] = array_values($stringMonths);
                $qforecast[] = array_values($stringForecast);

            }




            $vendorCode = $request->input('vendor_code');

            return view('purchasing.foremind_detail_print_customer',  [ // sedang bikin customer (report 1 nya)
                'monthm' => $monthm, // Ensure this is the correct data
                'materials' => $materials,
                'values' => $values,
                'mon' => $uniqueMonths,
                'vendorCode' => $vendorCode,
                'qforecast' => $qforecast,
                ])->render();
    }

    public function exportExcel($vendorCode)
    {

        // Function to get vendor information based on vendor code
        $getVendorInfo = function ($vendorCode) {
        return DB::table('forecast_material_predictions') // Replace with your actual table name
            ->where('vendor_code', $vendorCode)
            ->first();
        };

         // Get vendor information using the getVendorInfo function
            $vendorInfo = $getVendorInfo($vendorCode);
            $vendorName = $vendorInfo->vendor_name;

        // // Get data based on vendor code
        $materials = DB::table('forecast_material_predictions')
            ->where('vendor_code', $vendorCode)
            ->get();

        $forecasts = ForemindFinal::all();
        $transformedData = [];

                // Get unique months from all forecasts
        $allMonths = [];

        foreach ($forecasts as $forecast) {
            $dayForecast = Carbon::parse($forecast->day_forecast);
            $allMonths[] = $dayForecast->format('Y-m');
        }

        // Ensure unique months and sort them
        $uniqueMonths = array_unique($allMonths);
        sort($uniqueMonths);

         // Fetch your materials data from the database based on vendor code
            if ($vendorCode) {
                $materials = DB::table('forecast_material_predictions')
                    ->where('vendor_code', $vendorCode)
                    ->get();
            } else {
                $materials = []; // Empty array if no vendor code provided
            }

       $allmonth = [];
       foreach($materials as $material){
            $decodedMonths = json_decode($material->months, true);
            $stringMonths = json_decode($decodedMonths, true);
            $decodedForecast = json_decode($material->quantity_forecast, true);
            // dd($decodedForecast);
            $stringForecast = json_decode($decodedForecast, true);
            // dd($stringForecast);
            $truevalue[] = $stringMonths;

            //  if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedMonths)) {
            //     // Handle JSON decoding error
            //     dd('JSON Decoding Error:', json_last_error_msg());
            // }
              $decodedForecast = json_decode($material->quantity_forecast, true);
        // dd($decodedForecast);
        $stringForecast = json_decode($decodedForecast, true);
        // dd($stringForecast);
            $monthm[] = array_keys($stringMonths);
            $values[] = array_values($stringMonths);
            $qforecast[] = array_values($stringForecast);
            }



        // dd($qforecast);

        $export = new ForExport($monthm, $materials, $values, $uniqueMonths, $vendorCode, $qforecast,$vendorName);

        $fileName = $vendorName ? $vendorName . '_exported_data.xlsx' : 'filename.xlsx';

        return Excel::download($export,  $fileName);
    }


    public function exportExcelcustomer($vendorCode)
    {
        // dd($vendorCode);
        // Function to get vendor information based on vendor code
        $getVendorInfo = function ($vendorCode) {
        return DB::table('forecast_material_predictions') // Replace with your actual table name
            ->where('vendor_code', $vendorCode)
            ->first();
        };

        $vendorInfo = $getVendorInfo($vendorCode);
        $vendorName = $vendorInfo->vendor_name;

        // // Get data based on vendor code
        $materials = DB::table('forecast_material_predictions')
            ->where('vendor_code', $vendorCode)
            ->get();

        $forecasts = ForemindFinal::all();
        $transformedData = [];



                // Get unique months from all forecasts
        $allMonths = [];

        foreach ($forecasts as $forecast) {
            $dayForecast = Carbon::parse($forecast->day_forecast);
            $allMonths[] = $dayForecast->format('Y-m');
        }

        // Ensure unique months and sort them
        $uniqueMonths = array_unique($allMonths);
        sort($uniqueMonths);

         // Fetch your materials data from the database based on vendor code
            if ($vendorCode) {
                $materials = DB::table('forecast_material_predictions')
                    ->where('vendor_code', $vendorCode)
                    ->get();
            } else {
                $materials = []; // Empty array if no vendor code provided
            }

       $allmonth = [];
       foreach($materials as $material){
            $decodedMonths = json_decode($material->months, true);
            $stringMonths = json_decode($decodedMonths, true);
            $decodedForecast = json_decode($material->quantity_forecast, true);
            // dd($decodedForecast);
            $stringForecast = json_decode($decodedForecast, true);
            // dd($stringForecast);
            $truevalue[] = $stringMonths;

            //  if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedMonths)) {
            //     // Handle JSON decoding error
            //     dd('JSON Decoding Error:', json_last_error_msg());
            // }
              $decodedForecast = json_decode($material->quantity_forecast, true);
        // dd($decodedForecast);
        $stringForecast = json_decode($decodedForecast, true);
        // dd($stringForecast);
            $monthm[] = array_keys($stringMonths);
            $values[] = array_values($stringMonths);
            $qforecast[] = array_values($stringForecast);
            }



        // dd($qforecast);

        $export = new ForExportCustomer($monthm, $materials, $values, $uniqueMonths, $vendorCode, $qforecast, $vendorName);

        $fileName = $vendorName ? $vendorName . '_exported_data.xlsx' : 'filename.xlsx';

        return Excel::download($export,  $fileName);
    }


}
