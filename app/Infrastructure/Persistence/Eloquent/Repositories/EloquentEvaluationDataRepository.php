<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Discipline\Repositories\EvaluationDataRepositoryContract;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
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
        $query = Employee::where('dept_code', $deptNo);

        if (! empty($statuses)) {
            $query->whereIn('employment_scheme', $statuses);
        }

        $query->with(['evaluationData' => function ($q) use ($month, $year) {
            $q->whereMonth('Month', $month);
            if ($year) {
                $q->whereYear('Month', $year);
            }
        }]);

        return $query->get();
    }

    public function getByDepartmentCodes(array $codes): Collection
    {
        return Employee::whereIn('dept_code', $codes)
            ->where('employment_scheme', '!=', 'YAYASAN')
            ->where('grade_level', 5)
            ->get();
    }

    public function getYayasanEmployees(array $codes): Collection
    {
        return Employee::with('department')
            ->whereIn('dept_code', $codes)
            ->whereIn('employment_scheme', ['YAYASAN', 'YAYASAN KARAWANG'])
            ->get();
    }

    public function getMagangEmployees(array $codes): Collection
    {
        return Employee::whereIn('dept_code', $codes)
            ->whereIn('employment_scheme', ['MAGANG', 'MAGANG KARAWANG'])
            ->get();
    }

    public function getAllNonYayasan(): Collection
    {
        return Employee::where('employment_scheme', '!=', 'YAYASAN')
            ->where('grade_level', 5)
            ->get();
    }

    public function getAllYayasanEmployees(): Collection
    {
        return Employee::whereIn('employment_scheme', ['YAYASAN', 'YAYASAN KARAWANG'])
            ->get();
    }

    public function getAllMagangEmployees(): Collection
    {
        return Employee::whereIn('employment_scheme', ['MAGANG', 'MAGANG KARAWANG'])
            ->get();
    }

    public function findWithRelations(int $id): ?object
    {
        return EvaluationData::with(['karyawan', 'department'])->find($id);
    }
}
