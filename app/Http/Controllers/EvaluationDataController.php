<?php

namespace App\Http\Controllers;

use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use Illuminate\Http\Request;

class EvaluationDataController extends Controller
{
    public function evaluationformatrequestpageYayasan()
    {
        $statuses = Employee::where('employment_type', 'YAYASAN')->distinct()->pluck('employment_scheme');

        $departments = Department::whereHas('employees', function ($query) {
            $query->whereIn('employment_scheme', ['YAYASAN', 'YAYASAN KARAWANG']);
        })
            ->select('dept_no', 'name')
            ->distinct()
            ->get();

        return view('setting.formatrequestyayasan', compact('statuses', 'departments'));
    }

    public function evaluationformatrequestpageAllin()
    {
        $statuses = Employee::whereIn('employment_type', ['KONTRAK', 'TETAP'])
            ->distinct()
            ->pluck('employment_scheme');

        $departments = Department::whereHas('employees', function ($query) use ($statuses) {
            $query->whereIn('employment_scheme', $statuses);
        })
            ->select('dept_no', 'name')
            ->distinct()
            ->get();

        return view('setting.formatrequestallin', compact('statuses', 'departments'));
    }

    public function evaluationformatrequestpageMagang()
    {
        $statuses = Employee::whereIn('employment_type', ['MAGANG'])
            ->distinct()
            ->pluck('employment_scheme');

        $departments = Department::whereHas('employees', function ($query) use ($statuses) {
            $query->whereIn('employment_scheme', $statuses);
        })
            ->select('dept_no', 'name')
            ->distinct()
            ->get();

        return view('setting.formatrequestmagang', compact('statuses', 'departments'));
    }

    public function evaluationformatrequestpageAllinPerpanjangan()
    {
        $statuses = Employee::whereIn('employment_type', ['KONTRAK', 'TETAP'])
            ->distinct()
            ->pluck('employment_scheme');

        $departments = Department::whereHas('employees', function ($query) use ($statuses) {
            $query->whereIn('employment_scheme', $statuses);
        })
            ->select('dept_no', 'name')
            ->distinct()
            ->get();

        return view('setting.formatrequestallinperpanjangan', compact('statuses', 'departments'));
    }

    public function getFormatYearallin(Request $request)
    {
        $dept = $request->input('dept');
        $year = $request->input('year');

        $statuses = Employee::whereIn('employment_type', ['KONTRAK', 'TETAP'])
            ->distinct()
            ->pluck('employment_scheme');
        $magang = 0;
        // Get department codes where status is 'YAYASAN' or 'YAYASAN KARAWANG'
        $allowedDepartments = Employee::whereIn('employment_scheme', $statuses)->pluck('dept_code'); // Get department codes

        // Fetch employees who belong to the selected department and have the correct status
        $employees = Employee::with([
            'evaluationData' => function ($query) use ($year) {
                $query->whereYear('Month', $year);
            },
            'department',
        ])
            ->whereIn('dept_code', $allowedDepartments) // Ensure employees belong to the correct departments
            ->whereIn('employment_scheme', $statuses) // Ensure employees also have the correct status
            ->where('dept_code', $dept) // Filter by user-selected department
            ->whereNull('end_date') // Filter by user-selected department
            ->get();

        // Pass employees and the selected year to the view
        return view('test', compact('employees', 'year', 'magang'));
    }

    public function getFormatYearmagang(Request $request)
    {
        $dept = $request->input('dept');
        $year = $request->input('year');

        $statuses = Employee::whereIn('employment_type', ['MAGANG'])
            ->distinct()
            ->pluck('employment_scheme', 'start_date');
        $magang = 1;

        // Get department codes where status is 'YAYASAN' or 'YAYASAN KARAWANG'
        $allowedDepartments = Employee::whereIn('employment_scheme', $statuses)->pluck('dept_code'); // Get department codes

        // Fetch employees who belong to the selected department and have the correct status
        $employees = Employee::with([
            'evaluationData' => function ($query) use ($year) {
                $query->whereYear('Month', $year);
            },
            'department',
        ])
            ->whereIn('dept_code', $allowedDepartments) // Ensure employees belong to the correct departments
            ->whereIn('employment_scheme', $statuses) // Ensure employees also have the correct status
            ->where('dept_code', $dept) // Filter by user-selected department
            ->get();

        // Pass employees and the selected year to the view
        return view('test', compact('employees', 'year', 'magang'));
    }

    public function getFormatYearYayasan(Request $request)
    {
        // dd($request->all());
        $dept = $request->input('dept');
        $year = $request->input('year');
        $magang = 0;
        // Get department codes where status is 'YAYASAN' or 'YAYASAN KARAWANG'
        $allowedDepartments = Employee::whereIn('employment_scheme', ['YAYASAN', 'YAYASAN KARAWANG'])->pluck(
            'Dept',
        ); // Get department codes

        // Fetch employees who belong to the selected department and have the correct status
        $employees = Employee::with([
            'evaluationData' => function ($query) use ($year) {
                $query->whereYear('Month', $year);
            },
            'department',
        ])
            ->whereIn('dept_code', $allowedDepartments) // Ensure employees belong to the correct departments
            ->whereIn('employment_scheme', ['YAYASAN', 'YAYASAN KARAWANG']) // Ensure employees also have the correct status
            ->where('dept_code', $dept) // Filter by user-selected department
            ->get();

        // Pass employees and the selected year to the view
        return view('test', compact('employees', 'year', 'magang'));
    }

    public function getFormatYearallinPerpanjangan(Request $request)
    {
        $dept = $request->input('dept');
        $year = $request->input('year');

        $statuses = Employee::whereIn('employment_type', ['KONTRAK', 'TETAP'])
            ->distinct()
            ->pluck('employment_scheme');
        $magang = 0;
        // Get department codes where status is 'YAYASAN' or 'YAYASAN KARAWANG'
        $allowedDepartments = Employee::whereIn('employment_scheme', $statuses)->pluck('dept_code'); // Get department codes

        // Fetch employees who belong to the selected department and have the correct status
        $employees = Employee::with([
            'evaluationData' => function ($query) use ($year) {
                $query->whereYear('Month', $year);
            },
            'department',
        ])
            ->whereIn('dept_code', $allowedDepartments) // Ensure employees belong to the correct departments
            ->whereIn('employment_scheme', $statuses) // Ensure employees also have the correct status
            ->where('dept_code', $dept) // Filter by user-selected department
            ->get();

        // Pass employees and the selected year to the view
        return view('evaluasiPerpanjanganKaryawan', compact('employees', 'year', 'magang'));
    }
}
