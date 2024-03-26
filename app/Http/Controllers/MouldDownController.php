<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MtcMouldDown;
use App\DataTables\MtcMouldDownDataTable;

class MouldDownController extends Controller
{
    public function index(MtcMouldDownDataTable $dataTable)
    {
        return $dataTable->render("maintenance.mouldindex");
    }

    public function addmould(Request $request)
    {
         // Validate the incoming request data
         $validatedData = $request->validate([
            'mould_code' => 'required',
            'date_down' => 'required',
            'date_prediction' => 'required',
        ]);

        // Create a new InvLineList instance with the validated data
         // Create a new InvLineList instance with the validated data
         $line = MtcMouldDown::create([
            'mould_code' => $validatedData['mould_code'],
            'date_down' => $validatedData['date_down'],
            'date_prediction' => $validatedData['date_prediction'],
        ]);

        // Optionally, you can return a response indicating success
        return redirect()->route('moulddown.index')->with('success', 'Line added successfully');
    }
}
