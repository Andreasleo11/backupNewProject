<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Employee\Entities\Employee as EmployeeEntity;
use App\Domain\Employee\Repositories\EmployeeRepository;
use App\Infrastructure\Persistence\Eloquent\Models\Employee as EmployeeModel;

class EloquentEmployeeRepository implements EmployeeRepository
{
    public function findById(int $id): ?EmployeeEntity
    {
        $model = EmployeeModel::find($id);

        if (! $model) {
            return null;
        }

        return $this->toEntity($model);
    }

    /**
     * @return EmployeeEntity[]
     */
    public function search(string $term, int $limit = 10): array
    {
        $query = EmployeeModel::query();

        $query->where(function ($q) use ($term) {
            $q->where('nik', 'like', "%{$term}%")
              ->orWhere('name', 'like', "%{$term}%");
        });

        $models = $query
            ->orderBy('nik')
            ->limit($limit)
            ->get();

        return $models
            ->map(fn (EmployeeModel $model) => $this->toEntity($model))
            ->all();
    }

    /**
     * @return Employee[]
     */
    public function findByIds(array $ids): array
    {
        if(empty($ids)) {
            return [];
        }

        $models = EmployeeModel::whereIn('id', $ids)->get();
        return $models
            ->map(fn (EmployeeModel $model) => $this->toEntity($model))
            ->all();
    }

    private function toEntity(EmployeeModel $model): EmployeeEntity
    {
        return new EmployeeEntity(
            id: $model->id,
            nik: $model->nik,
            name: $model->name,
            branch: $model->branch,
            deptCode: $model->dept_code,
            startDate: $model->start_date ? new \DateTimeImmutable($model->start_date->format('Y-m-d')) : null,
            endDate: $model->end_date ? new \DateTimeImmutable($model->end_date->format('Y-m-d')) : null,
        );
    }
}
