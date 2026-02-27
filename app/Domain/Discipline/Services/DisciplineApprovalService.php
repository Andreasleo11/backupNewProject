<?php

namespace App\Domain\Discipline\Services;

use App\Models\EvaluationData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class DisciplineApprovalService
{
    /**
     * Approve employees by department head.
     *
     * @param string $deptNo Department number
     * @param int $month Month to filter
     * @param int $year Year to filter
     * @param bool $lockData Whether to lock the data after approval
     * @return int Number of employees approved
     */
    public function approveDeptHead(
        string $deptNo,
        int $month,
        int $year,
        bool $lockData = false
    ): int {
        $employees = $this->getYayasanEmployeesByDept($deptNo, $month, $year);

        foreach ($employees as $employee) {
            $employee->depthead = Auth::user()->name;

            if ($lockData) {
                $employee->is_lock = 1;
            }

            $employee->save();
        }

        return $employees->count();
    }

    /**
     * Approve employees by general manager/HRD.
     *
     * @param string $deptNo Department number
     * @param int $month Month to filter
     * @param int|null $year Year to filter (optional)
     * @return int Number of employees approved
     */
    public function approveGeneralManager(
        string $deptNo,
        int $month,
        ?int $year = null
    ): int {
        $employees = $this->getYayasanEmployeesByDept($deptNo, $month, $year);

        foreach ($employees as $employee) {
            $employee->generalmanager = Auth::user()->name;
            $employee->save();
        }

        return $employees->count();
    }

    /**
     * Reject employees by department head.
     *
     * @param string $deptNo Department number
     * @param int $month Month to filter
     * @param int $year Year to filter
     * @param string|null $remark Rejection remark
     * @return int Number of employees rejected
     */
    public function rejectDeptHead(
        string $deptNo,
        int $month,
        int $year,
        ?string $remark = null
    ): int {
        $employees = $this->getYayasanEmployeesByDept($deptNo, $month, $year);

        foreach ($employees as $employee) {
            $employee->depthead = 'rejected';

            if ($remark) {
                $employee->remark = $remark;
            }

            $employee->save();
        }

        return $employees->count();
    }

    /**
     * Reject employees by HRD (rejects both depthead and GM).
     *
     * @param string $deptNo Department number
     * @param int $month Month to filter
     * @param int $year Year to filter
     * @param string|null $remark Rejection remark
     * @return int Number of employees rejected
     */
    public function rejectHRD(
        string $deptNo,
        int $month,
        int $year,
        ?string $remark = null
    ): int {
        $employees = $this->getYayasanEmployeesByDept($deptNo, $month, $year);

        foreach ($employees as $employee) {
            $employee->depthead = 'rejected';
            $employee->generalmanager = 'rejected';

            if ($remark) {
                $employee->remark = $remark;
            }

            $employee->save();
        }

        return $employees->count();
    }

    /**
     * Get Yayasan employees by department, month, and optional year.
     *
     * @param string $deptNo Department number
     * @param int $month Month to filter
     * @param int|null $year Year to filter (optional)
     */
    private function getYayasanEmployeesByDept(
        string $deptNo,
        int $month,
        ?int $year = null
    ): Collection {
        $query = EvaluationData::whereHas('karyawan', function ($query) use ($deptNo) {
            $query->where('dept_code', $deptNo)
                ->whereIn('employment_scheme', ['YAYASAN', 'YAYASAN KARAWANG']);
        })->whereMonth('Month', $month);

        if ($year) {
            $query->whereYear('Month', $year);
        }

        return $query->get();
    }

    /**
     * Reset approvals for previously rejected evaluation data.
     * This allows the record to be resubmitted for approval.
     *
     * @param EvaluationData $evaluationData The evaluation data to reset
     * @return bool Whether approvals were reset
     */
    public function resetRejectedApprovals(EvaluationData $evaluationData): bool
    {
        if (
            $evaluationData->generalmanager === 'rejected' ||
            $evaluationData->depthead === 'rejected'
        ) {
            $evaluationData->update([
                'depthead' => null,
                'generalmanager' => null,
            ]);

            return true;
        }

        return false;
    }
}
