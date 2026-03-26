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
     * Build the A/B grade collection for the JPayroll Excel download.
     * Scores ≥ 91 → nilai_A, 71–90 → nilai_B, <71 → neither.
     * Employees on probation (<6 months) are excluded.
     *
     * @return array<array{employee_id: string, nilai_A: int, nilai_B: int}>
     */
    public function exportJpayrollCollection(int $month, int $year): array
    {
        $cutoffDate = Carbon::createFromDate($year, $month, 1)
            ->copy()
            ->subMonths(6)
            ->startOfMonth();

        $records = EvaluationData::with('karyawan')
            ->whereHas('karyawan', function ($q) use ($cutoffDate) {
                $q->whereIn('employment_scheme', ['YAYASAN', 'YAYASAN KARAWANG'])
                  ->where('start_date', '<', $cutoffDate);
            })
            ->whereMonth('Month', $month)
            ->whereYear('Month', $year)
            ->get();

        $result = [];
        foreach ($records as $record) {
            $nik = $record->karyawan?->nik;
            if (! $nik) continue;

            $result[$nik] ??= ['employee_id' => $nik, 'nilai_A' => 0, 'nilai_B' => 0];

            $total = (int) $record->total;
            if ($total >= 91) {
                $result[$nik]['nilai_A'] = 1;
                $result[$nik]['nilai_B'] = 0;
            } elseif ($total >= 71) {
                $result[$nik]['nilai_A'] = 0;
                $result[$nik]['nilai_B'] = 1;
            }
        }

        return array_values($result);
    }
}
