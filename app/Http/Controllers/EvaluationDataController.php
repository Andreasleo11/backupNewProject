<?php

namespace App\Http\Controllers;

use App\DataTables\EvaluationDataDataTable;
use App\Exports\EvaluationDataExp;
use App\Imports\EvaluationDataImport;
use App\Domain\Evaluation\Services\EvaluationDepartmentStatusService;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use App\Models\EvaluationData;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

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

    public function getDepartmentStatusYayasan(Request $request)
    {
        try {
            $statusService = app(EvaluationDepartmentStatusService::class);

            $month = $request->input('month') ?? $request->input('filter_month');
            $year  = $request->input('year') ?? $request->input('filter_year');

            $departmentStatus = $statusService->getDepartmentStatusForMonth($month, $year);

            return response()->json([
                'status' => 'success',
                'data'   => $departmentStatus,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function dateExport()
    {
        return view('setting.inputDateExportYayasan');
    }

    public function exportYayasanJpayroll(Request $request)
    {
        $statusService = app(EvaluationDepartmentStatusService::class);

        $selectedMonth    = (int) ($request->input('month') ?? $request->input('filter_status'));
        $currentYear      = (int) $request->input('year');
        $departmentStatus = $statusService->getJpayrollDepartmentStatus($selectedMonth, $currentYear);

        return view('setting.exportYayasanJpayroll', compact('departmentStatus', 'selectedMonth', 'currentYear'));
    }

    public function exportYayasanJpayrollFunction(Request $request)
    {
        $selectedMonth = (int) $request->input('filter_status');
        $currentYear   = (int) $request->input('year');

        $cutoffDate = \Carbon\Carbon::createFromDate($currentYear, $selectedMonth, 1)
            ->copy()
            ->subMonths(6)
            ->startOfMonth();

        $employees = EvaluationData::with('karyawan')
            ->whereHas('karyawan', function ($q) use ($cutoffDate) {
                $q->whereIn('employment_scheme', ['YAYASAN', 'YAYASAN KARAWANG'])
                  ->where('start_date', '<', $cutoffDate);
            })
            ->whereMonth('Month', $selectedMonth)
            ->whereYear('Month', $currentYear)
            ->get();

        // Categorize each employee's total into A/B tiers for JPayroll
        $result = [];
        foreach ($employees as $data) {
            $nik = $data->karyawan?->nik;
            if (! $nik) continue;

            if (! isset($result[$nik])) {
                $result[$nik] = ['employee_id' => $nik, 'nilai_A' => 0, 'nilai_B' => 0];
            }

            $total = $data->total;
            if ($total >= 91) {
                $result[$nik]['nilai_A'] = 1;
                $result[$nik]['nilai_B'] = 0;
            } elseif ($total >= 71) {
                $result[$nik]['nilai_A'] = 0;
                $result[$nik]['nilai_B'] = 1;
            }
        }

        $currentDate = \Carbon\Carbon::now()->format('d-m-y');
        $fileName    = "DataYayasan_{$currentDate}.xlsx";

        return Excel::download(
            new \App\Exports\YayasanEvaluationExport(array_values($result)),
            $fileName
        );
    }
}
