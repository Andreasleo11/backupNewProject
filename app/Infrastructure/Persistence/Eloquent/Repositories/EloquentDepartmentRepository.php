<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Department\Entities\Department as DepartmentEntity;
use App\Domain\Department\Repositories\DepartmentRepository;
use App\Infrastructure\Persistence\Eloquent\Models\Department as EloquentDepartment;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentDepartmentRepository implements DepartmentRepository
{
    public function paginate(
        ?string $search,
        ?string $branch,
        ?bool $onlyActive,
        int $perPage = 10
    ): LengthAwarePaginator {
        $query = EloquentDepartment::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}")
                    ->orWhere('name', 'like', "%{$search}");
            });
        }

        if ($branch) {
            $query->where('branch', $branch);
        }

        if ($onlyActive === true) {
            $query->where('is_active', true);
        }

        $query->orderBy('branch')->orderBy('code');

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?DepartmentEntity
    {
        $model = EloquentDepartment::find($id);

        return $model ? $this->mapToEntity($model) : null;
    }

    public function create(DepartmentEntity $department): DepartmentEntity
    {
        $model = EloquentDepartment::create([
            'dept_no' => $department->deptNo(),
            'name' => $department->name(),
            'code' => $department->code(),
            'branch' => $department->branch(),
            'is_office' => $department->isOffice(),
            'is_active' => $department->isActive(),
        ]);

        return $this->mapToEntity($model);
    }

    public function update(DepartmentEntity $department): DepartmentEntity
    {
        $model = EloquentDepartment::findOrFail($department->id());
        $model->update([
            'dept_no' => $department->deptNo(),
            'name' => $department->name(),
            'code' => $department->code(),
            'branch' => $department->branch(),
            'is_office' => $department->isOffice(),
            'is_active' => $department->isActive(),
        ]);

        return $this->mapToEntity($model);
    }

    public function toggleActive(int $id): void
    {
        $model = EloquentDepartment::findOrFail($id);
        $model->is_active = ! $model->is_active;
        $model->save();
    }

    private function mapToEntity(EloquentDepartment $model): DepartmentEntity
    {
        return new DepartmentEntity(
            id: $model->id,
            deptNo: $model->dept_no,
            name: $model->name,
            code: $model->code,
            branch: $model->branch,
            isOffice: $model->is_office,
            isActive: $model->is_active,
        );
    }
}
