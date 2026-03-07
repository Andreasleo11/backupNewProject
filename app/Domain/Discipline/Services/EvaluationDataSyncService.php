<?php

namespace App\Domain\Discipline\Services;

use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use App\Models\EvaluationData;
use App\Models\EvaluationDataWeekly;

class EvaluationDataSyncService
{
    /**
     * Sync department field in EvaluationData from Employee master data.
     *
     * @return int Number of records updated
     */
    public function syncDepartmentsFromEmployees(): int
    {
        $evaluationDataRecords = EvaluationData::all();
        $updatedCount = 0;

        foreach ($evaluationDataRecords as $evaluationData) {
            $employee = Employee::where('nik', $evaluationData->nik)->first();

            if ($employee && $evaluationData->dept !== $employee->dept_code) {
                $evaluationData->dept = $employee->dept_code;
                $evaluationData->save();
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    /**
     * Sync department field in EvaluationDataWeekly from Employee master data.
     *
     * @return int Number of records updated
     */
    public function syncDepartmentsInWeeklyData(): int
    {
        $weeklyDataRecords = EvaluationDataWeekly::with('karyawan')->get();
        $updatedCount = 0;

        foreach ($weeklyDataRecords as $weeklyData) {
            if ($weeklyData->karyawan && $weeklyData->dept !== $weeklyData->karyawan->dept_code) {
                $weeklyData->dept = $weeklyData->karyawan->dept_code;
                $weeklyData->save();
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    /**
     * Sync departments from EvaluationData using employee relationships.
     * This is an alternative approach that uses eager loading.
     *
     * @return int Number of records updated
     */
    public function syncDepartmentsUsingRelationships(): int
    {
        $datas = EvaluationData::with('karyawan')->get();
        $weeklyDatas = EvaluationDataWeekly::with('karyawan')->get();
        $updatedCount = 0;

        foreach ($datas as $data) {
            if ($data->karyawan && $data->dept !== $data->karyawan->dept_code) {
                $data->dept = $data->karyawan->dept_code;
                $data->save();
                $updatedCount++;
            }
        }

        foreach ($weeklyDatas as $weeklyData) {
            if ($weeklyData->karyawan && $weeklyData->dept !== $weeklyData->karyawan->dept_code) {
                $weeklyData->dept = $weeklyData->karyawan->dept_code;
                $weeklyData->save();
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    /**
     * Sync all department data across both tables.
     *
     * @return array Statistics about sync operation
     */
    public function syncAllDepartments(): array
    {
        $evaluationCount = $this->syncDepartmentsFromEmployees();
        $weeklyCount = $this->syncDepartmentsInWeeklyData();

        return [
            'evaluation_data_updated' => $evaluationCount,
            'weekly_data_updated' => $weeklyCount,
            'total_updated' => $evaluationCount + $weeklyCount,
        ];
    }
}
