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
        
        $datas = InvLineList::get();
        // dd($datas);
        return $dataTable->render("sap.linelist", compact('datas'));
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
    
    public function editline(Request $request, $linecode)
    {
        $newlines = InvLineList::where('line_code', $linecode)->get();

        foreach($newlines as $newline){
        // dd($newline)
        $newline->where('line_code', $request->line_code)->update([
            'line_name' => $request->line_name,
            'departement' => $request->departement,
            'daily_minutes' => $request->daily_minutes,
        ]);
        }

        return redirect()->route('invlinelist')->with(['success' => 'User updated successfully!']);

    }

    public function deleteline($linecode)
    {
        InvLineList::where('line_code', $linecode)->delete();
        return redirect()->back()->with(['success' => 'User deleted successfully!']);

    }
}
