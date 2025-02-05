<?php

namespace App\View\Components;

use App\DataTables\EmployeeWithEvaluationDataTable;
use App\Models\Employee;
use App\Models\EvaluationData;
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

    /**
     * Create a new component instance.
     */

    public function __construct()
    {
        // Fetch all employees
        $employees = Employee::all();
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
        $this->departmentEmployeeCounts = Employee::join('departments', 'employees.Dept', '=', 'departments.dept_no')
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
        $this->monthYearOptions = EvaluationData::select('Month')
            ->distinct()
            ->get()
            ->map(fn ($item) => [
                'value' => Carbon::parse($item->Month)->format('m-Y'),
                'name' => Carbon::parse($item->Month)->format('F Y'),
            ]);

        // Initial employee data
        $this->employeeData = [
            'total' => Employee::distinct('NIK')->count(),
            'alpha' => EvaluationData::sum('Alpha'),
            'telat' => EvaluationData::sum('Telat'),
            'izin'  => EvaluationData::sum('Izin'),
            'sakit' => EvaluationData::sum('Sakit'),
        ];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.employee-dashboard');
    }
}
