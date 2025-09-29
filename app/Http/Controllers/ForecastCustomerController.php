<?php

namespace App\Http\Controllers;

use App\DataTables\ForecastCustomerMasterDataTable;
use App\Models\ForecastCustomerMaster;
use Illuminate\Http\Request;

class ForecastCustomerController extends Controller
{
    public function index(ForecastCustomerMasterDataTable $dataTable)
    {
        // return view('purchasing.forecastcustomerindex');
        return $dataTable->render('purchasing.forecastcustomerindex');
    }

    public function addnewmaster(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'forecast_code' => 'required',
            'forecast_name' => 'required',
            'customer' => 'required',
        ]);

        // Create a new InvLineList instance with the validated data
        // Create a new InvLineList instance with the validated data
        $line = ForecastCustomerMaster::create([
            'forecast_code' => $validatedData['forecast_code'],
            'forecast_name' => $validatedData['forecast_name'],
            'customer' => $validatedData['customer'],
        ]);

        // Optionally, you can return a response indicating success
        return redirect()->route('fc.index')->with('success', 'Line added successfully');
    }
}
