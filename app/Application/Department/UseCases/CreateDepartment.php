<?php

namespace App\Application\Department\UseCases;

use App\Application\Department\DTOs\DepartmentData;
use App\Domain\Department\Entities\Department;
use App\Domain\Department\Repositories\DepartmentRepository;

class CreateDepartment
{
    public function __construct(
        private readonly DepartmentRepository $departments
    ){}

    public function execute(DepartmentData $data): Department
    {
        $entity = new Department(
            id: null,
            deptNo: $data->deptNo,
            name: $data->name,
            code: $data->code,
            branch: $data->branch,
            isOffice: $data->isOffice,
            isActive: $data->isActive,
        );

        return $this->departments->create($entity);
    }
}