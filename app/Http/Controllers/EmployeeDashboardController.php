<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeWithEvaluationDataTable;
use App\Models\Employee;
use App\Models\EvaluationData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeDashboardController extends Controller
{
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

    /**
     * Get employee count grouped by department with status as columns.
     *
     * @return array
     */
    private function getEmployeeCountByDepartment()
    {
        // Get all unique statuses across employees
        $statuses = Employee::distinct()->pluck('employee_status')->toArray();

        // Fetch employee count grouped by department and status
        $departments = Employee::join('departments', 'employees.Dept', '=', 'departments.dept_no')
            ->select(
                'departments.name as department_name',
                'employees.employee_status',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('departments.name', 'employees.employee_status')
            ->get();

        // Initialize department-wise result structure
        $result = [];

        foreach ($departments as $department) {
            $deptName = $department->department_name;

            // Ensure department entry exists
            if (!isset($result[$deptName])) {
                $result[$deptName] = [
                    'label' => $deptName,
                    'breakdown' => [],
                    'total_count' => 0
                ];
            }

            // Assign the count to the correct status column
            $result[$deptName]['breakdown'][$department->employee_status] = (int) $department->count;
            $result[$deptName]['total_count'] += (int) $department->count;
        }

        return $result;
    }

    /**
     * Controller Index Method
     */
    public function index(EmployeeWithEvaluationDataTable $dataTable)
    {
        $departmentEmployeeCounts = $this->getEmployeeCountByDepartment();

        return $dataTable->render('employee-dashboard', compact('departmentEmployeeCounts'));
    }

    /**
     * Get employee count grouped by month for a given year.
     *
     * @param int|null $year
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployeeCountByMonth($year = null)
    {
        $year = $year ?? date('Y'); // Default to current year if not provided

        try {
            $employeeCounts = Employee::join('evaluation_datas', 'employees.NIK', '=', 'evaluation_datas.NIK')
                ->whereYear('evaluation_datas.Month', $year)
                ->select(
                    DB::raw('MONTH(evaluation_datas.Month) as month'),
                    DB::raw('COUNT(DISTINCT employees.NIK) as total_count')
                )
                ->groupBy(DB::raw('MONTH(evaluation_datas.Month)'))
                ->orderBy(DB::raw('MONTH(evaluation_datas.Month)'))
                ->get();

            // Log SQL Query for debugging
            \Illuminate\Support\Facades\Log::info("Employee Count Query for Year: $year", ['query' => $employeeCounts->toArray()]);

            // Ensure all 12 months are included with default count 0
            $formattedCounts = array_fill(1, 12, 0);
            foreach ($employeeCounts as $count) {
                $formattedCounts[$count->month] = $count->total_count;
            }

            return response()->json($formattedCounts);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error fetching employee counts: " . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

}
