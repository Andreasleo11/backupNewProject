<?php

namespace App\Domain\Evaluation\Services;

use App\Models\EvaluationData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class EvaluationLegacyApprovalService
{
    /**
     * Approve evaluation records for a department by the department head.
     * Works for all employee types (Regular, Yayasan, Magang).
     *
     * @param string $deptNo Department code
     * @param int $month Month number
     * @param int $year Year
     * @param bool $lockData Whether to also lock the records (Regular flow)
     * @return int Number of records approved
     */
    public function approveDeptHead(
        string $deptNo,
        int $month,
        int $year,
        bool $lockData = false
    ): int {
        $employees = $this->getEmployeesByDept($deptNo, $month, $year);

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
     * Approve evaluation records as General Manager (Magang flow).
     *
     * @param string $deptNo Department code
     * @param int $month Month number
     * @param int|null $year Year (optional)
     * @return int Number of records approved
     */
    public function approveGeneralManager(
        string $deptNo,
        int $month,
        ?int $year = null
    ): int {
        $employees = $this->getEmployeesByDept($deptNo, $month, $year);

        foreach ($employees as $employee) {
            $employee->generalmanager = Auth::user()->name;
            $employee->save();
        }

        return $employees->count();
    }

    /**
     * Approve evaluation records as HRD (Yayasan final approval step).
     * Semantically the same as GM approval but named clearly for the Yayasan flow.
     *
     * @param string $deptNo Department code
     * @param int $month Month number
     * @param int|null $year Year (optional)
     * @return int Number of records approved
     */
    public function approveHrd(
        string $deptNo,
        int $month,
        ?int $year = null
    ): int {
        return $this->approveGeneralManager($deptNo, $month, $year);
    }

    /**
     * Reject evaluation records as the department head.
     *
     * @param string $deptNo Department code
     * @param int $month Month number
     * @param int $year Year
     * @param string|null $remark Rejection note
     * @return int Number of records rejected
     */
    public function rejectDeptHead(
        string $deptNo,
        int $month,
        int $year,
        ?string $remark = null
    ): int {
        $employees = $this->getEmployeesByDept($deptNo, $month, $year);

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
     * Reject evaluation records as HRD (resets both depthead and GM fields).
     *
     * @param string $deptNo Department code
     * @param int $month Month number
     * @param int $year Year
     * @param string|null $remark Rejection note
     * @return int Number of records rejected
     */
    public function rejectHRD(
        string $deptNo,
        int $month,
        int $year,
        ?string $remark = null
    ): int {
        $employees = $this->getEmployeesByDept($deptNo, $month, $year);

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
     * Reset approval fields so a previously-rejected record can re-enter the flow.
     *
     * @param EvaluationData $evaluationData The record to reset
     * @return bool Whether a reset was performed
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

    /**
     * Get evaluation records by department + month (and optionally year).
     * Type-neutral: does not filter by employment_scheme — works for all employee types.
     *
     * @param string $deptNo Department code
     * @param int $month Month number
     * @param int|null $year Year (optional)
     */
    private function getEmployeesByDept(
        string $deptNo,
        int $month,
        ?int $year = null
    ): Collection {
        $query = EvaluationData::where('dept', $deptNo)
            ->whereMonth('Month', $month);

        if ($year) {
            $query->whereYear('Month', $year);
        }

        return $query->get();
    }
}
