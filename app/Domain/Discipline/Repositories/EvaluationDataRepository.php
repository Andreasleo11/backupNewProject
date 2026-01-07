<?php

namespace App\Domain\Discipline\Repositories;

use App\Models\EvaluationData;
use Illuminate\Support\Collection;

class EvaluationDataRepository
{
    /**
     * Get evaluation data by department codes with optional filters.
     */
    public function getByDepartmentCodes(
        array $codes,
        bool $excludeYayasan = true,
        ?int $level = 5
    ): Collection {
        return EvaluationData::with('karyawan')
            ->whereHas('karyawan', function ($query) use ($codes, $excludeYayasan, $level) {
                $query->whereIn('Dept', $codes);

                if ($excludeYayasan) {
                    $query->where('status', '!=', 'YAYASAN');
                }

                if ($level !== null) {
                    $query->where('level', $level);
                }
            })
            ->get();
    }

    /**
     * Get Yayasan employees by department codes.
     */
    public function getYayasanEmployees(array $codes): Collection
    {
        return EvaluationData::with('karyawan', 'department')
            ->whereHas('karyawan', function ($query) use ($codes) {
                $query->whereIn('Dept', $codes)
                    ->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']);
            })
            ->get();
    }

    /**
     * Get Magang (internship) employees by department codes.
     */
    public function getMagangEmployees(array $codes): Collection
    {
        return EvaluationData::with('karyawan')
            ->whereHas('karyawan', function ($query) use ($codes) {
                $query->whereIn('Dept', $codes)
                    ->whereIn('status', ['MAGANG', 'MAGANG KARAWANG']);
            })
            ->get();
    }

    /**
     * Get all employees (excluding Yayasan) by department codes.
     */
    public function getAllNonYayasan(int $level = 5): Collection
    {
        return EvaluationData::with('karyawan')
            ->whereHas('karyawan', function ($query) use ($level) {
                $query->where('status', '!==', 'YAYASAN')
                    ->where('level', $level);
            })
            ->get();
    }

    /**
     * Get Yayasan employees filtered by cutoff date.
     */
    public function getYayasanByCutoffDate(array $codes, string $cutoffDate, int $month): Collection
    {
        return EvaluationData::with('karyawan')
            ->whereHas('karyawan', function ($query) use ($codes, $cutoffDate) {
                $query->whereIn('Dept', $codes)
                    ->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG'])
                    ->where('start_date', '<', $cutoffDate);
            })
            ->whereMonth('month', $month)
            ->get();
    }

    /**
     * Get evaluation data filtered by department and month.
     */
    public function getByDepartmentAndMonth(
        string $deptNo,
        int $month,
        ?int $year = null,
        array $statuses = []
    ): Collection {
        $query = EvaluationData::whereHas('karyawan', function ($query) use ($deptNo, $statuses) {
            $query->where('Dept', $deptNo);

            if (!empty($statuses)) {
                $query->whereIn('status', $statuses);
            }
        })->whereMonth('Month', $month);

        if ($year) {
            $query->whereYear('Month', $year);
        }

        return $query->get();
    }

    /**
     * Get all Yayasan evaluation data filtered by month and year.
     */
    public function getYayasanByMonthAndYear(int $month, int $year): Collection
    {
        return EvaluationData::whereHas('karyawan', function ($query) {
            $query->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']);
        })
            ->whereMonth('Month', $month)
            ->whereYear('Month', $year)
            ->get();
    }
}
