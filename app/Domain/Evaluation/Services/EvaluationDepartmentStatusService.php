<?php

namespace App\Domain\Evaluation\Services;

use App\Domain\Evaluation\Repositories\EvaluationDataRepositoryContract;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Models\EvaluationData;
use Carbon\Carbon;

class EvaluationDepartmentStatusService
{
    public function __construct(
        private EvaluationDataRepositoryContract $repository
    ) {}

    /**
     * Get department status for Yayasan employees by month and year.
     * Determines which departments are "Ready" or "Not Ready" based on approval status.
     *
     * @return array ['Department Name' => 'Ready'|'Not Ready']
     */
    public function getDepartmentStatusForMonth(int $month, int $year): array
    {
        $selectedDate = Carbon::createFromDate($year, $month, 1);
        $cutoffDate = $selectedDate->copy()->subMonths(6)->startOfMonth();

        // Get all employees for the month (within cutoff date)
        $employees = EvaluationData::with('karyawan')
            ->whereHas('karyawan', function ($query) use ($cutoffDate) {
                $query
                    ->whereIn('employment_scheme', ['YAYASAN', 'YAYASAN KARAWANG'])
                    ->where('start_date', '<', $cutoffDate);
            })
            ->whereMonth('month', $month)
            ->whereYear('month', $year)
            ->get()
            ->groupBy('dept');

        // Get employees with approved data (depthead approved)
        $approvedData = EvaluationData::with('karyawan')
            ->whereHas('karyawan', function ($query) use ($cutoffDate) {
                $query
                    ->whereIn('employment_scheme', ['YAYASAN', 'YAYASAN KARAWANG'])
                    ->where('start_date', '<', $cutoffDate);
            })
            ->whereMonth('month', $month)
            ->whereYear('month', $year)
            ->whereNotNull('depthead')
            ->where('depthead', '!=', '')
            ->get()
            ->groupBy('dept');

        $departments = Department::pluck('name', 'dept_no');

        $departmentStatus = [];
        foreach ($employees as $dept_no => $employeeGroup) {
            $departmentName = $departments->get($dept_no, 'Unknown Department');
            $departmentStatus[$departmentName] = isset($approvedData[$dept_no])
                ? 'Ready'
                : 'Not Ready';
        }

        return $departmentStatus;
    }

    /**
     * Get J-payroll export status data for departments.
     * Similar to getDepartmentStatusForMonth but returns more detailed data.
     */
    public function getJpayrollDepartmentStatus(int $month, int $year): array
    {
        return $this->getDepartmentStatusForMonth($month, $year);
    }
}
