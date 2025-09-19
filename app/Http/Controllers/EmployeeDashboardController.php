<?php

namespace App\Http\Controllers;

use App\DataTables\EmployeeDataTable;
use App\DataTables\EmployeeWithEvaluationDataTable;
use App\Jobs\SyncEmployeesJob;
use App\Models\Employee;
use App\Models\EvaluationDataWeekly;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeDashboardController extends Controller
{
    protected $employeeWithEvaluationDataTable;
    protected $employeeDataTable;

    public function __construct(
        EmployeeWithEvaluationDataTable $dataTable1,
        EmployeeDataTable $dataTable2,
    ) {
        $this->employeeWithEvaluationDataTable = $dataTable1;
        $this->employeeDataTable = $dataTable2;
    }

    public function index()
    {
        $departmentEmployeeCounts = $this->getEmployeeCountByDepartment();

        $dataTableEmployeeWithEvaluation = $this->employeeWithEvaluationDataTable->html();

        return view(
            "employee-dashboard",
            compact("departmentEmployeeCounts", "dataTableEmployeeWithEvaluation"),
        );
    }

    public function getEmployeesData()
    {
        return $this->employeeDataTable->render("employee-dashboard");
    }

    public function getEmployeeWithEvaluationData()
    {
        return $this->employeeWithEvaluationDataTable->render("employee-dashboard");
    }

    public function filterEmployees(Request $request)
    {
        $selectedMonthYear = $request->input("monthYear");
        $selectedWeek = $request->input("week");
        $selectedBranch = $request->input("branch");
        $selectedDepartment = $request->input("department");
        $selectedStatus = $request->input("status");
        $selectedGender = $request->input("gender");

        if (auth()->user()->department->name !== "MANAGEMENT") {
            $selectedDepartment =
                auth()->user()->department->dept_no ?? $request->input("department");
        }

        $activeEmployeesQuery = Employee::query();

        // Apply filters for Employees table
        if ($selectedBranch) {
            $activeEmployeesQuery->where("Branch", $selectedBranch);
        }
        if ($selectedDepartment) {
            $activeEmployeesQuery->where("Dept", $selectedDepartment);
        }
        if ($selectedStatus) {
            $activeEmployeesQuery->where("employee_status", $selectedStatus);
        }
        if ($selectedGender) {
            $activeEmployeesQuery->where("Gender", $selectedGender);
        }

        $activeEmployees = $activeEmployeesQuery->whereNull("end_date")->pluck("NIK")->toArray(); // Get filtered NIKs

        $employeeData = [
            "total" => count($activeEmployees), // Total employees after filters
            "alpha" => 0,
            "telat" => 0,
            "izin" => 0,
            "sakit" => 0,
        ];

        // Base Query for filtering
        $evaluationQuery = EvaluationDataWeekly::whereIn("NIK", $activeEmployees);

        if (!empty($selectedMonthYear)) {
            [$selectedMonth, $selectedYear] = explode("-", $selectedMonthYear);

            $evaluationQuery
                ->whereMonth("Month", $selectedMonth)
                ->whereYear("Month", $selectedYear);
        }

        // If Week is selected, filter by week
        if (!empty($selectedWeek)) {
            [$year, $weekNumber] = explode("-W", $selectedWeek);

            // Get the start and end dates of the selected week
            $startDate = Carbon::now()
                ->setISODate($year, $weekNumber)
                ->startOfWeek()
                ->toDateString();
            $endDate = Carbon::now()->setISODate($year, $weekNumber)->endOfWeek()->toDateString();

            // \Illuminate\Support\Facades\Log::info("startDate : $startDate");
            // \Illuminate\Support\Facades\Log::info("endDate: $endDate");

            // Apply week range filter
            $evaluationQuery->whereBetween("Month", [$startDate, $endDate]);
        }

        // Count employees per category only if value > 0
        $employeeData["alpha"] = (clone $evaluationQuery)
            ->where("Alpha", ">", 0)
            ->distinct()
            ->count("NIK");
        $employeeData["telat"] = (clone $evaluationQuery)
            ->where("Telat", ">", 0)
            ->distinct()
            ->count("NIK");
        $employeeData["izin"] = (clone $evaluationQuery)
            ->where("Izin", ">", 0)
            ->distinct()
            ->count("NIK");
        $employeeData["sakit"] = (clone $evaluationQuery)
            ->where("Sakit", ">", 0)
            ->distinct()
            ->count("NIK");

        return response()->json($employeeData);
    }

    public function getEmployeesByCategory(Request $request)
    {
        $category = strtolower($request->category);
        $monthYear = $request->monthYear;
        $branch = $request->branch;
        $department = $request->department;
        $status = $request->status;
        $gender = $request->gender;
        $week = $request->week;

        if (!in_array($category, ["alpha", "telat", "izin", "sakit"])) {
            return response()->json([]);
        }

        // \Illuminate\Support\Facades\Log::info($request->all());

        $query = EvaluationDataWeekly::join(
            "employees",
            "evaluation_data_weekly.NIK",
            "=",
            "employees.NIK",
        )
            ->join("departments", "employees.Dept", "=", "departments.dept_no")
            ->select(
                "employees.NIK",
                "employees.Nama",
                "employees.Gender",
                "departments.name as department_name",
                "employees.employee_status",
                DB::raw("SUM(evaluation_data_weekly.$category) as category_count"),
            );

        if (!empty($monthYear)) {
            [$month, $year] = explode("-", $monthYear);
            $query
                ->whereMonth("evaluation_data_weekly.Month", $month)
                ->whereYear("evaluation_data_weekly.Month", $year)
                ->where("evaluation_data_weekly.$category", ">", 0)
                ->orderByDesc("evaluation_data_weekly.$category") // Order by highest count first
                ->groupBy(
                    "employees.NIK",
                    "employees.Nama",
                    "employees.Gender",
                    "departments.name",
                    "employees.employee_status",
                    "evaluation_data_weekly.$category",
                ); // Ensure uniqueness
        }

        // Apply Week filter (Extracting Start & End Date)
        if (!empty($week)) {
            [$year, $weekNumber] = explode("-W", $week);

            // Get the start and end dates of the selected week
            $startDate = Carbon::now()
                ->setISODate($year, $weekNumber)
                ->startOfWeek()
                ->toDateString();
            $endDate = Carbon::now()->setISODate($year, $weekNumber)->endOfWeek()->toDateString();

            // Apply the filter
            $query->whereBetween("evaluation_data_weekly.Month", [$startDate, $endDate]);
        }

        if (!empty($branch)) {
            $query->where("employees.Branch", $branch);
        }

        if (!empty($department)) {
            $query->where("employees.Dept", $department);
        }

        if (!empty($status)) {
            $query->where("employees.employee_status", $status);
        }

        if (!empty($gender)) {
            $query->where("employees.Gender", $gender);
        }

        // Only fetch employees with non-zero evaluation count in selected category
        $query
            ->having("category_count", ">", 0)
            ->groupBy(
                "employees.NIK",
                "employees.Nama",
                "employees.Gender",
                "departments.name",
                "employees.employee_status",
            )
            ->orderByDesc("category_count");

        $employees = $query->get();

        return response()->json($employees);
    }

    public function getEmployeesByDepartment(Request $request)
    {
        $department = $request->department;
        $status = $request->status; // Status can be null if user clicks "Total Employees"

        $query = Employee::whereNull("end_date")
            ->join("departments", "employees.Dept", "=", "departments.dept_no")
            ->where("departments.name", $department)
            ->select(
                "employees.NIK",
                "employees.Nama",
                "employees.Branch",
                "employees.employee_status",
            );

        // Apply status filter only if a specific status is selected
        if ($status) {
            $query->where("employees.employee_status", $status);
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
        $query = Employee::whereNull("end_date")
            ->join("departments", "employees.Dept", "=", "departments.dept_no")
            ->select(
                "employees.NIK",
                "employees.Nama",
                "departments.name as department_name",
                "employees.employee_status",
            );

        // Apply filters
        if (!empty($branch)) {
            $query->where("employees.Branch", $branch);
        }
        if (!empty($dept)) {
            $query->where("departments.dept_no", $dept);
        }
        if (!empty($status)) {
            $query->where("employees.employee_status", $status);
        }
        if (!empty($gender)) {
            $query->where("employees.Gender", $gender);
        }

        // Determine what category was clicked
        if ($legend === "Branch") {
            $query->where("employees.Branch", $category);
        } elseif ($legend === "Dept") {
            $query->where("departments.name", $category);
        } elseif ($legend === "Status") {
            $query->where("employees.employee_status", $category);
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
        $statuses = Employee::whereNull("end_date")
            ->distinct()
            ->pluck("employee_status")
            ->toArray();

        // Fetch employee count grouped by department and status
        $departments = Employee::join("departments", "employees.Dept", "=", "departments.dept_no")
            ->select(
                "departments.name as department_name",
                "employees.employee_status",
                DB::raw("COUNT(*) as count"),
            )
            ->groupBy("departments.name", "employees.employee_status")
            ->get();

        // Initialize department-wise result structure
        $result = [];

        foreach ($departments as $department) {
            $deptName = $department->department_name;

            // Ensure department entry exists
            if (!isset($result[$deptName])) {
                $result[$deptName] = [
                    "label" => $deptName,
                    "breakdown" => [],
                    "total_count" => 0,
                ];
            }

            // Assign the count to the correct status column
            $result[$deptName]["breakdown"][$department->employee_status] =
                (int) $department->count;
            $result[$deptName]["total_count"] += (int) $department->count;
        }

        return $result;
    }

    /**
     * Get employee count grouped by month for a given year.
     *
     * @param int|null $year
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployeeCountByMonth($year = null)
    {
        $year = $year ?? date("Y"); // Default to current year if not provided

        try {
            $employeeCounts = Employee::whereNull("end_date")
                ->join("evaluation_datas", "employees.NIK", "=", "evaluation_datas.NIK")
                ->whereYear("evaluation_datas.Month", $year)
                ->select(
                    DB::raw("MONTH(evaluation_datas.Month) as month"),
                    DB::raw("COUNT(DISTINCT employees.NIK) as total_count"),
                )
                ->groupBy(DB::raw("MONTH(evaluation_datas.Month)"))
                ->orderBy(DB::raw("MONTH(evaluation_datas.Month)"))
                ->get();

            // Log SQL Query for debugging
            \Illuminate\Support\Facades\Log::info("Employee Count Query for Year: $year", [
                "query" => $employeeCounts->toArray(),
            ]);

            // Ensure all 12 months are included with default count 0
            $formattedCounts = array_fill(1, 12, 0);
            foreach ($employeeCounts as $count) {
                $formattedCounts[$count->month] = $count->total_count;
            }

            return response()->json($formattedCounts);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error(
                "Error fetching employee counts: " . $e->getMessage(),
            );
            return response()->json(["error" => "Internal Server Error"], 500);
        }
    }

    public function getWeeklyEvaluationData(Request $request, $year, $week)
    {
        $startDate = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $endDate = Carbon::now()->setISODate($year, $week)->endOfWeek();

        $query = EvaluationDataWeekly::join(
            "employees",
            "evaluation_data_weekly.NIK",
            "=",
            "employees.NIK",
        )
            ->join("departments", "employees.Dept", "=", "departments.dept_no")
            ->whereBetween("evaluation_data_weekly.Month", [$startDate, $endDate]);

        // **Apply Filters if Provided**
        if ($request->has("branch") && $request->branch) {
            $query->where("employees.Branch", $request->branch);
        }
        if ($request->has("department") && $request->department) {
            $query->where("departments.dept_no", $request->department);
        }
        if ($request->has("status") && $request->status) {
            $query->where("employees.employee_status", $request->status);
        }
        if ($request->has("gender") && $request->gender) {
            $query->where("employees.Gender", $request->gender);
        }

        $data = $query
            ->select(
                "departments.name as department_name",
                DB::raw("COUNT(DISTINCT CASE WHEN Alpha > 0 THEN employees.NIK END) as Alpha"),
                DB::raw("COUNT(DISTINCT CASE WHEN Telat > 0 THEN employees.NIK END) as Telat"),
                DB::raw("COUNT(DISTINCT CASE WHEN Izin > 0 THEN employees.NIK END) as Izin"),
                DB::raw("COUNT(DISTINCT CASE WHEN Sakit > 0 THEN employees.NIK END) as Sakit"),
                DB::raw(
                    "COUNT(CASE WHEN (Alpha > 0 OR Telat > 0 OR Izin > 0 OR Sakit > 0) THEN 1 END) as total_count",
                ),
            )
            ->groupBy("departments.name")
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->department_name => [
                        "label" => $item->department_name,
                        "breakdown" => [
                            "Alpha" => $item->Alpha,
                            "Telat" => $item->Telat,
                            "Izin" => $item->Izin,
                            "Sakit" => $item->Sakit,
                        ],
                        "total_count" => $item->total_count,
                    ],
                ];
            })
            ->toArray();

        return response()->json($data);
    }

    public function getEmployeesByCategoryAndWeek($department, $category, $year, $week)
    {
        $category = strtolower($category);
        $validCategories = ["alpha", "telat", "izin", "sakit"];

        // Initialize total category sum
        $totalCategorySum = 0;

        $query = EvaluationDataWeekly::join(
            "employees",
            "evaluation_data_weekly.NIK",
            "=",
            "employees.NIK",
        )
            ->join("departments", "employees.Dept", "=", "departments.dept_no")
            ->whereYear("Month", $year)
            ->where(DB::raw("WEEK(Month, 1)"), $week)
            ->where("departments.name", $department);

        if ($category === "total") {
            // Retrieve all employees where ANY category > 0
            $query->where(function ($q) use ($validCategories) {
                foreach ($validCategories as $cat) {
                    $q->orWhere("evaluation_data_weekly.$cat", ">", 0);
                }
            });

            // Select only basic employee data without category_total
            $query->select(
                "employees.NIK",
                "employees.Nama",
                "departments.name as department_name",
                "employees.employee_status",
            );
        } else {
            if (!in_array($category, $validCategories)) {
                return response()->json([]); // Return empty if invalid category
            }

            // Add category total only if a valid category is selected
            $query
                ->where("evaluation_data_weekly.$category", ">", 0)
                ->select(
                    "employees.NIK",
                    "employees.Nama",
                    "departments.name as department_name",
                    "employees.employee_status",
                    DB::raw("SUM(evaluation_data_weekly.$category) as category_total"),
                );
        }

        $query->groupBy(
            "employees.NIK",
            "employees.Nama",
            "departments.name",
            "employees.employee_status",
        );

        $employees = $query->get();

        // Calculate total sum of selected category (only if category is not "total")
        if ($category !== "total") {
            $totalCategorySum = $employees->sum("category_total");
        }

        return response()->json([
            "employees" => $employees,
            "total_selected_category" => $category !== "total" ? $totalCategorySum : null,
        ]);
    }

    public function updateEmployeeData()
    {
        SyncEmployeesJob::dispatch("10000", now()->year);

        return redirect()
            ->back()
            ->with("success", "Sync job dispatched successfully. You can check progress shortly.");
    }
}
