<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\foremindFinal;
use App\Models\PurchaseRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\PurchasingContact;

class PurchasingController extends Controller
{
    public function index()
    {
        $statuses = [
            'approved' => 4,
            'rejected' => 5,
            'waitingDeptHead' => 1,
            'waitingPurchaser' => 6,
            'waitingGm' => 7,
            'waitingVerificator' => 2,
            'waitingDirector' => 3,
        ];

        $data = [];

        foreach ($statuses as $key => $status) {
            $data[$key] = PurchaseRequest::where('status', $status)
                                        ->where('to_department', 'Purchasing')
                                        ->whereHas('createdBy', function($query){
                                            $query->orWhere('id', auth()->user()->id);
                                        })
                                        ->orWhere('from_department', 'Purchasing')
                                        ->get()->count();
        }

        $twoDaysAgo = Carbon::now()->subDays(2);

        $prOver2Days = PurchaseRequest::where('status', $status)
            ->where('to_department', 'Purchasing')
            ->whereHas('createdBy', function($query){
                $query->orWhere('id', auth()->user()->id);
            })
            ->whereDate('created_at', '<=', $twoDaysAgo)
            ->get();

        return view('purchasing.purchasing_landing', compact('data', 'prOver2Days'));
    }

    public function indexhome()
    {
         // Retrieve forecasts from the foremindFinal table
         $forecasts = ForemindFinal::all();
         $transformedData = [];
         $contacts = PurchasingContact::all();

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
        $materials = DB::table('forecast_material_predictions')->paginate(10);
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

// dd($qforecast);

            return view('purchasing.foremind_detail',  [
                // 'monthm' => $monthm, // Ensure this is the correct data
                'materials' => $materials,
                'values' => $values,
                'mon' => $uniqueMonths,
                'qforecast' => $qforecast,
                'contacts' =>$contacts,
            ]);
    }

}
