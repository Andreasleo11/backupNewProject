<?php

namespace App\View\Components;

use App\Models\Employee;
use App\Models\EvaluationDataWeekly;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\View\Component;

class EmployeeDashboard extends Component
{
    public $chartData;
    public $departmentEmployeeCounts;
    public $monthYearOptions;
    public $employeeData;
    public $employees;
    public $latestYear;
    public $dataTableEmployee;
    public $latestWeek;
    public $weeklyEvaluationData;
    public $latestUpdatedAt;
    public $authUser;

    /**
     * Create a new component instance.
     */

    public function __construct(\App\DataTables\EmployeeDataTable $employeeDataTable)
    {
        $this->dataTableEmployee = $employeeDataTable->html();

        $this->authUser = auth()->user();

        // Fetch all employees
        $employeesQuery = Employee::whereNull('end_date');
        $user = $this->authUser;

        if($user && $user->department && $user->department->name !== 'MANAGEMENT'){
            $employeesQuery->whereHas('department', function($query) use ($user){
                $query->where('id', $user->department->id);
            });
        }

        $employees = $employeesQuery->get();
        $this->employees = $employees;

        // Prepare chart data
        $this->chartData = $employees->map(function ($employee) {
            return [
                'Branch' => $employee->Branch,
                'Dept' => [
                    'dept_no' => $employee->department->dept_no ?? null,
                    'name' => $employee->department->name ?? null,
                ],
                'Status' => $employee->employee_status,
                'Gender' => $employee->Gender,
            ];
        });

        // Department-wise employee count with status breakdown
        $this->departmentEmployeeCounts = $employeesQuery->join('departments', 'employees.Dept', '=', 'departments.dept_no')
            ->select('departments.name as department_name', 'employees.employee_status', DB::raw('COUNT(*) as count'))
            ->groupBy('departments.name', 'employees.employee_status')
            ->get()
            ->groupBy('department_name')
            ->map(function ($group) {
                $breakdown = $group->mapWithKeys(fn ($item) => [$item->employee_status => $item->count]);

                return [
                    'label' => $group->first()->department_name,
                    'breakdown' => $breakdown,
                    'total_count' => $breakdown->sum()
                ];
            });

        // Month-Year options for filtering
        $this->monthYearOptions = EvaluationDataWeekly::select('Month')
            ->distinct()
            ->get()
            ->map(fn ($item) => [
                'value' => Carbon::parse($item->Month)->format('m-Y'),
                'name' => Carbon::parse($item->Month)->format('F Y'),
            ])
            ->unique('value')
            ->values();

        // Initial employee data
        $this->employeeData = [
            'total' => $employees->count(),
            'alpha' => EvaluationDataWeekly::sum('Alpha'),
            'telat' => EvaluationDataWeekly::sum('Telat'),
            'izin'  => EvaluationDataWeekly::sum('Izin'),
            'sakit' => EvaluationDataWeekly::sum('Sakit'),
        ];

        // Get the latest year available from evaluation_datas table
        $latestYear = EvaluationDataWeekly::selectRaw('YEAR(Month) as year')
            ->orderByDesc('year')
            ->limit(1)
            ->value('year');

        // If no data exists, default to the current year
        $this->latestYear = $latestYear ?? date('Y');

        // 1) Get the latest row from evaluation_data_weekly
        $latestRecord = EvaluationDataWeekly::orderBy('Month', 'desc')->first();

        if ($latestRecord) {
            $date = Carbon::parse($latestRecord->Month);
            $this->latestWeek = $date->format('o-\WW');
        }

        // Fetch aggregated employee evaluation data with department names
        $this->weeklyEvaluationData = EvaluationDataWeekly::join('employees', 'evaluation_data_weekly.NIK', '=', 'employees.NIK')
                ->join('departments', 'employees.Dept', '=', 'departments.dept_no')
                ->select(
                    'departments.name as department_name',
                    DB::raw('SUM(Alpha) as Alpha'),
                    DB::raw('SUM(Telat) as Telat'),
                    DB::raw('SUM(Izin) as Izin'),
                    DB::raw('SUM(Sakit) as Sakit')
                )
                ->groupBy('departments.name')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [
                        $item->department_name => [
                            'label' => $item->department_name,
                            'breakdown' => [
                                'Alpha' => $item->Alpha,
                                'Telat' => $item->Telat,
                                'Izin' => $item->Izin,
                                'Sakit' => $item->Sakit,
                            ],
                            'total_count' => $item->Alpha + $item->Telat + $item->Izin + $item->Sakit,
                        ]
                    ];
                })
                ->toArray();

        $latest = EvaluationDataWeekly::orderBy('updated_at', 'desc')->first();

        $this->latestUpdatedAt = $latest
            ? $latest->updated_at->timezone('Asia/Jakarta')->translatedFormat('l, d F Y H:i:s')
            : null;

    }
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.employee-dashboard');
    }
}
