<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\foremindFinal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; 

class PurchasingController extends Controller
{
    public function index()
    {
        return view('purchasing.purchasing_landing');
    }

    public function indexhome()
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



        // Fetch your materials data from the database
        $materials = DB::table('forecast_material_predictions')->get();
        $allmonth = [];
        foreach($materials as $material){
        $decodedForecast = json_decode($material->quantity_forecast, true);
        // dd($decodedForecast);
        $stringForecast = json_decode($decodedForecast, true);
        // dd($stringForecast);
        $decodedMonths = json_decode($material->months, true);
        $stringMonths = json_decode($decodedMonths, true);
        $truevalue[] = $stringMonths;
        
        $monthm[] = array_keys($stringMonths);
        $values[] = array_values($stringMonths);
        $qforecast[] = array_values($stringForecast);
        // dd($qforecast);
        $combinedArray = array(array_values($stringForecast),array_values($stringMonths));
        // dd($combinedArray);

        }

            return view('purchasing.foremind_detail',  [
                'monthm' => $monthm, // Ensure this is the correct data
                'materials' => $materials,
                'values' => $values,
                'mon' => $uniqueMonths,
                'qforecast' => $qforecast,
                
            ]);
    }
    
}
