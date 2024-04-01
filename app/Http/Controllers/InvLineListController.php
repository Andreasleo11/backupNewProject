<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; 

use App\Models\InvLineList;
use App\DataTables\InvLineListDataTable;

class InvLineListController extends Controller
{
    public function index(InvLineListDataTable $dataTable)
    {   
        return $dataTable->render("sap.linelist");
    }


    public function addline(Request $request)
    {
         // Validate the incoming request data
         $validatedData = $request->validate([
            'line_code' => 'required',
            'line_name' => 'required',
            'departement' => 'required',
            'daily_minutes' => 'required',
        ]);

        // Create a new InvLineList instance with the validated data
         // Create a new InvLineList instance with the validated data
         $line = InvLineList::create([
            'line_code' => $validatedData['line_code'],
            'line_name' => $validatedData['line_name'],
            'departement' => $validatedData['departement'],
            'daily_minutes' => $validatedData['daily_minutes'],
        ]);

        // Optionally, you can return a response indicating success
        return redirect()->route('invlinelist')->with('success', 'Line added successfully');
    }
    
}
