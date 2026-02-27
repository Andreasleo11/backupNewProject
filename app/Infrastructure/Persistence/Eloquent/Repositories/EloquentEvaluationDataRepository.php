<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Discipline\Repositories\EvaluationDataRepositoryContract;
use App\Models\EvaluationData;
use Illuminate\Support\Collection;

final class EloquentEvaluationDataRepository implements EvaluationDataRepositoryContract
{
    public function getByDepartmentAndMonth(
        string $deptNo,
        int $month,
        ?int $year = null,
        array $statuses = []
    ): Collection {
        $query = EvaluationData::whereHas('karyawan', function ($query) use ($deptNo, $statuses) {
            $query->where('dept_code', $deptNo);

            if (! empty($statuses)) {
                $query->whereIn('employment_scheme', $statuses);
            }
        })->whereMonth('Month', $month);

        if ($year) {
            $query->whereYear('Month', $year);
        }

        return $query->get();
    }

    public function getYayasanByMonthAndYear(int $month, int $year): Collection
    {
        return EvaluationData::whereHas('karyawan', function ($query) {
            $query->whereIn('employment_scheme', ['YAYASAN', 'YAYASAN KARAWANG']);
        })
            ->whereMonth('Month', $month)
            ->whereYear('Month', $year)
            ->get();
    }

    public function getByDepartmentCodes(array $codes): Collection
    {
        return EvaluationData::with('karyawan')
            ->whereHas('karyawan', function ($query) use ($codes) {
                $query->whereIn('dept_code', $codes)
                    ->where('employment_scheme', '!=', 'YAYASAN')
                    ->where('grade_level', 5);
            })
            ->get();
    }

    public function getYayasanEmployees(array $codes): Collection
    {
        return EvaluationData::with('karyawan', 'department')
            ->whereHas('karyawan', function ($query) use ($codes) {
                $query->whereIn('dept_code', $codes)
                    ->whereIn('employment_scheme', ['YAYASAN', 'YAYASAN KARAWANG']);
            })
            ->get();
    }

    public function getMagangEmployees(array $codes): Collection
    {
        return EvaluationData::with('karyawan')
            ->whereHas('karyawan', function ($query) use ($codes) {
                $query->whereIn('dept_code', $codes)
                    ->whereIn('employment_scheme', ['MAGANG', 'MAGANG KARAWANG']);
            })
            ->get();
    }

    public function getAllNonYayasan(): Collection
    {
        return EvaluationData::with('karyawan')
            ->whereHas('karyawan', function ($query) {
                $query->where('employment_scheme', '!==', 'YAYASAN')
                    ->where('grade_level', 5);
            })
            ->get();
    }

    public function getAllYayasanEmployees(): Collection
    {
        return EvaluationData::with('karyawan')
            ->whereHas('karyawan', function ($query) {
                $query->whereIn('employment_scheme', ['YAYASAN', 'YAYASAN KARAWANG']);
            })
            ->get();
    }

    public function findWithRelations(int $id): ?object
    {
        return EvaluationData::with(['karyawan', 'department'])->find($id);
    }
}
