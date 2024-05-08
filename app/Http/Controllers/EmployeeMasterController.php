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

}
