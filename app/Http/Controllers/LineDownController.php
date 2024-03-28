<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MtcLineDown;
use App\Models\InvLineList;
use App\DataTables\MtcLineDownDataTable;

class LineDownController extends Controller
{
    public function index(MtcLineDownDataTable $dataTable)
    {
        $data = InvLineList::pluck('line_code')->unique();
        return $dataTable->render("maintenance.lineindex", compact("data"));
    }

    public function addlinedown(Request $request)
    {
        // dd($request->all());
         // Validate the incoming request data
         $validatedData = $request->validate([
            'line_code' => 'required',
            'date_down' => 'required',
            'date_prediction' => 'required',
        ]);

        
         $line = MtcLineDown::create([
            'line_code' => $validatedData['line_code'],
            'date_down' => $validatedData['date_down'],
            'date_prediction' => $validatedData['date_prediction'],
        ]);

        // Optionally, you can return a response indicating success
        return redirect()->route('linedown.index')->with('success', 'Line added successfully');
    }
}
