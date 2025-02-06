<?php

namespace App\Http\Controllers\director;

use App\DataTables\EmployeeWithEvaluationDataTable;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PurchaseOrder;
use App\Models\MonthlyBudgetReport;
use App\Models\MonthlyBudgetSummaryReport;
use App\Models\PurchaseRequest;
use App\Models\EmployeeWarningLog;
use App\Models\EvaluationData;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DirectorHomeController extends Controller
{
    public function index(EmployeeWithEvaluationDataTable $dataTable, Request $request)
    {
        $reportCounts = [
            'approved' => Report::approved()->count(),
            'waiting' => Report::waitingApproval()->count(),
            'rejected' => Report::rejected()->count(),
        ];

        $purchaseRequestCounts = [
            'approved' => PurchaseRequest::approved()->count(),
            'waiting' => PurchaseRequest::waiting()->count(),
            'rejected' => PurchaseRequest::rejected()->count(),
        ];

        $monthlyBudgetReportsCounts = [
            'approved' => MonthlyBudgetReport::approvedByDirector()->count(),
            'waiting' => MonthlyBudgetReport::waiting()->count(),
            'rejected' => MonthlyBudgetReport::rejected()->count(),
        ];

        $monthlyBudgetSummaryReportsCounts = [
            'approved' => MonthlyBudgetSummaryReport::approved()->count(),
            'waiting' => MonthlyBudgetSummaryReport::waitingDirector()->count(),
            'rejected' => MonthlyBudgetSummaryReport::rejected()->count(),
        ];

        $poCounts = [
            'approved' => PurchaseOrder::approved()->count(),
            'waiting' => PurchaseOrder::waiting()->count(),
            'rejected' => PurchaseOrder::rejected()->count(),
        ];

        return $dataTable->render('director.home', compact(
            'reportCounts',
            'purchaseRequestCounts',
            'monthlyBudgetReportsCounts',
            'monthlyBudgetSummaryReportsCounts',
            'poCounts',
        ));
    }

    public function filterEmployees(Request $request)
    {
        $selectedMonthYear = $request->input('monthYear');

        $activeEmployees = Employee::pluck('NIK')->toArray(); // Get existing NIKs from employees table

        if ($selectedMonthYear) {
            [$selectedMonth, $selectedYear] = explode('-', $selectedMonthYear);


            $employeeData = [
                'total' => Employee::distinct('NIK')->count(),
                'alpha' => EvaluationData::whereIn('NIK', $activeEmployees)
                                        ->whereMonth('Month', $selectedMonth)
                                        ->whereYear('Month', $selectedYear)
                                        ->where('Alpha', '>', 0) // Count only employees who have Alpha > 0
                                        ->distinct()
                                        ->count('NIK'),
                'telat' => EvaluationData::whereIn('NIK', $activeEmployees)
                                        ->whereMonth('Month', $selectedMonth)
                                        ->whereYear('Month', $selectedYear)
                                        ->where('Telat', '>', 0) // Count only employees who have Telat > 0
                                        ->distinct()
                                        ->count('NIK'),
                'izin'  => EvaluationData::whereIn('NIK', $activeEmployees)
                                        ->whereMonth('Month', $selectedMonth)
                                        ->whereYear('Month', $selectedYear)
                                        ->where('Izin', '>', 0) // Count only employees who have Izin > 0
                                        ->distinct()
                                        ->count('NIK'),
                'sakit' => EvaluationData::whereIn('NIK', $activeEmployees)
                                        ->whereMonth('Month', $selectedMonth)
                                        ->whereYear('Month', $selectedYear)
                                        ->where('Sakit', '>', 0) // Count only employees who have Sakit > 0
                                        ->distinct()
                                        ->count('NIK'),
            ];
        } else {
            $employeeData = [
                'total' => Employee::distinct('NIK')->count(),
                'alpha' => EvaluationData::whereIn('NIK', $activeEmployees)->sum('Alpha'),
                'telat' => EvaluationData::whereIn('NIK', $activeEmployees)->sum('Telat'),
                'izin'  => EvaluationData::whereIn('NIK', $activeEmployees)->sum('Izin'),
                'sakit' => EvaluationData::whereIn('NIK', $activeEmployees)->sum('Sakit'),
            ];
        }


        return response()->json($employeeData);
    }

    public function storeWarningLog(Request $request)
    {
        $request->validate([
            'NIK' => 'required',
            'warning_type' => 'required',
            'reason' => 'required',
        ]);

        EmployeeWarningLog::create($request->all());

        return redirect()->back()->with('success', 'Warning log has been created');
    }

    public function getEmployeesByCategory(Request $request)
    {
        $category = strtolower($request->category); // Convert to lowercase
        $monthYear = $request->monthYear; // Get selected month-year filter

        if (!in_array($category, ['alpha', 'telat', 'izin', 'sakit'])) {
            return response()->json([]);
        }

        $query = EvaluationData::join('employees', 'evaluation_datas.NIK', '=', 'employees.NIK')
                    ->join('departments', 'employees.Dept', '=', 'departments.dept_no')
                    ->select(
                        'employees.NIK',
                        'employees.Nama',
                        'departments.name as department_name',
                        'employees.employee_status',
                        "evaluation_datas.$category as category_count"
                    );
        // Apply Month-Year filter if selected
        if (!empty($monthYear)) {
            [$month, $year] = explode('-', $monthYear);
            $query->whereMonth('evaluation_datas.Month', $month)
                    ->whereYear('evaluation_datas.Month', $year)
                    ->where("evaluation_datas.$category", '>', 0)
                    ->orderByDesc("evaluation_datas.$category") // Order by highest count first
                    ->groupBy('employees.NIK', 'employees.Nama', 'departments.name', 'employees.employee_status', "evaluation_datas.$category"); // Ensure uniqueness
        }

        $employees = $query->get();

        return response()->json($employees);
    }

    public function getEmployeesByDepartment(Request $request)
    {
        $department = $request->department;
        $status = $request->status; // Status can be null if user clicks "Total Employees"

        $query = Employee::join('departments', 'employees.Dept', '=', 'departments.dept_no')
            ->where('departments.name', $department)
            ->select('employees.NIK', 'employees.Nama', 'employees.employee_status');

        // Apply status filter only if a specific status is selected
        if ($status) {
            $query->where('employees.employee_status', $status);
        }

        $employees = $query->get();

        return response()->json($employees);
    }

    public function getEmployeesByChartCategory(Request $request)
    {
        $category = $request->category;
        $branch = $request->branch;
        $dept = $request->dept;
        $status = $request->status;
        $gender = $request->gender;
        $legend = $request->legend;

        // Base query
        $query = Employee::join('departments', 'employees.Dept', '=', 'departments.dept_no')
            ->select('employees.NIK', 'employees.Nama', 'departments.name as department_name', 'employees.employee_status');

        // Apply filters
        if (!empty($branch)) {
            $query->where('employees.Branch', $branch);
        }
        if (!empty($dept)) {
            $query->where('departments.dept_no', $dept);
        }
        if (!empty($status)) {
            $query->where('employees.employee_status', $status);
        }
        if (!empty($gender)) {
            $query->where('employees.Gender', $gender);
        }

        // Determine what category was clicked
        if ($legend === "Branch") {
            $query->where('employees.Branch', $category);
        } elseif ($legend === "Dept") {
            $query->where('departments.name', $category);
        } elseif ($legend === "Status") {
            $query->where('employees.employee_status', $category);
        }

        $employees = $query->get();

        return response()->json($employees);
    }

}
