<?php

namespace App\Domain\Discipline\Services;

use App\Models\Employee;
use App\Models\EvaluationData;
use App\Models\EvaluationDataWeekly;

class DisciplineDataSyncService
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
            $employee = Employee::where('NIK', $evaluationData->NIK)->first();

            if ($employee && $evaluationData->dept !== $employee->Dept) {
                $evaluationData->dept = $employee->Dept;
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
            if ($weeklyData->karyawan && $weeklyData->dept !== $weeklyData->karyawan->Dept) {
                $weeklyData->dept = $weeklyData->karyawan->Dept;
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
            if ($data->karyawan && $data->dept !== $data->karyawan->Dept) {
                $data->dept = $data->karyawan->Dept;
                $data->save();
                $updatedCount++;
            }
        }

        foreach ($weeklyDatas as $weeklyData) {
            if ($weeklyData->karyawan && $weeklyData->dept !== $weeklyData->karyawan->Dept) {
                $weeklyData->dept = $weeklyData->karyawan->Dept;
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
