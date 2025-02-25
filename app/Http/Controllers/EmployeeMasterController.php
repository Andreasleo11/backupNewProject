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
            'Gender' => 'required',
            'Dept' => 'required',
            'start_date' => 'required',
            'status' => 'required',
        ]);

        Employee::create([
            'NIK' => $validatedData['NIK'],
            'Nama' => $validatedData['Nama'],
            'Gender' => $validatedData['Gender'],
            'Dept' => $validatedData['Dept'],
            'start_date' => $validatedData['start_date'],
            'status' => $validatedData['status'],
        ]);

        return redirect()->route('index.employeesmaster')->with('success', 'Line added successfully');

    }



    public function editemployee(Request $request, $id)
    {
        $newemployee = Employee::where('id', $id)->first();
        // dd($newemployee);

        if (!$newemployee) {
            return redirect()->route('index.employeesmaster')->with(['error' => 'User not found!']);
        }

        $updateData = [
            'Nama' => $request->Nama,
            'Gender' => $request->Gender,
            'Dept' => $request->Dept,
            'status' => $request->status,
            'end_date' => $request->end_date,
            'jatah_cuti_tahun' => $request->jatah_cuti_tahun,
        ];

        // If end_date is not null, set employee_status to "NOT ACTIVE"
        if (!is_null($request->end_date)) {
            $updateData['employee_status'] = 'NOT ACTIVE';
        }

        // Update the employee record
        $newemployee->update($updateData);

        return redirect()->route('index.employeesmaster')->with(['success' => 'User updated successfully!']);

    }

    public function deleteemployee($id)
    {
        Employee::where('id', $id)->delete();
        return redirect()->back()->with(['success' => 'User deleted successfully!']);

    }


}
