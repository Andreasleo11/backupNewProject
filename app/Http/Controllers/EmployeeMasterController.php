<?php

namespace App\Http\Controllers;

use App\Imports\AnnualLeaveQuotaImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeMasterController extends Controller
{
    public function showImportForm()
    {
        return view('employee.import');
    }

    public function importAnnualLeaveQuota(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls',
        ]);

        Excel::import(new AnnualLeaveQuotaImport, $request->file('file'));

        return back()->with('success', 'Annual leave quotas updated successfully!');
    }
}
