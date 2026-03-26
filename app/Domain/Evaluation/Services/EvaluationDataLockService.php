<?php

namespace App\Domain\Evaluation\Services;

use App\Models\EvaluationData;
use Illuminate\Support\Collection;

class EvaluationDataLockService
{
    /**
     * Lock evaluation data for a department and month.
     *
     * @param string $deptNo Department number
     * @param int $month Month to filter
     * @return int Number of records locked
     */
    public function lockByDepartmentAndMonth(string $deptNo, int $month): int
    {
        $employees = EvaluationData::whereHas('karyawan', function ($query) use ($deptNo) {
            $query->where('dept_code', $deptNo);
        })
            ->whereMonth('Month', $month)
            ->get();

        foreach ($employees as $employee) {
            $employee->is_lock = true;
            $employee->save();
        }

        return $employees->count();
    }

    /**
     * Get all locked evaluation data with employee information.
     */
    public function getLockedData(): Collection
    {
        return EvaluationData::with('karyawan')
            ->where('is_lock', true)
            ->get();
    }

    /**
     * Unlock specific evaluation data record.
     *
     * @param int $id Evaluation data ID
     * @return bool Success status
     */
    public function unlock(int $id): bool
    {
        $evaluationData = EvaluationData::find($id);

        if (! $evaluationData) {
            return false;
        }

        $evaluationData->is_lock = false;
        $evaluationData->save();

        return true;
    }

    /**
     * Unlock multiple evaluation data records.
     *
     * @param array $ids Array of evaluation data IDs
     * @return int Number of records unlocked
     */
    public function unlockMultiple(array $ids): int
    {
        return EvaluationData::whereIn('id', $ids)
            ->update(['is_lock' => false]);
    }
}
