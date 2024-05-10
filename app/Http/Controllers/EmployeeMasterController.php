<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeDataTable;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeMasterController extends Controller
{
    public function index(EmployeeDataTable $dataTable)
    {
        $datas = Employee::get();
        return $dataTable->render("setting.employeeindex", compact("datas"));
        
    }

    public function addemployee(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'NIK' => 'required',
            'Nama' => 'required',
            'Dept' => 'required',
            'start_date' => 'required',
            'status' => 'required',
        ]);

        $employe = Employee::create([
            'NIK' => $validatedData['NIK'],
            'Nama' => $validatedData['Nama'],
            'Dept' => $validatedData['Dept'],
            'start_date' => $validatedData['start_date'],
            'status' => $validatedData['status'],
        ]);

        return redirect()->route('index.employeesmaster')->with('success', 'Line added successfully');

    }



    public function editemployee(Request $request, $id)
    {
        $newemployees = Employee::where('id', $id)->get();
        // dd($newemployee);

        foreach($newemployees as $newemployee){
        // dd($newline)
        $newemployee->where('id', $request->id)->update([
            'Nama' => $request->Nama,
            'Dept' => $request->Dept,
            'status' => $request->status,
        ]);
        }

        return redirect()->route('index.employeesmaster')->with(['success' => 'User updated successfully!']);

    }

    public function deleteemployee($id)
    {
        Employee::where('id', $id)->delete();
        return redirect()->back()->with(['success' => 'User deleted successfully!']);

    }
    

}
