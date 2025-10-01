<?php

namespace App\Http\Controllers;

use App\Imports\EmployeeJabatanImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeController extends Controller
{
    public function showImportForm()
    {
        return view('employees.import-jabatan');
    }

    public function importJabatan(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls',
        ]);

        Excel::import(new EmployeeJabatanImport, $request->file('file'));

        return back()->with('success', 'Jabatan updated successfully!');
    }
}
