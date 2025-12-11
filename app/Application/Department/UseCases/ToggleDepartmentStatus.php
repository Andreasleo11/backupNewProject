<?php

namespace App\Application\Department\UseCases;

use App\Application\Department\DTOs\DepartmentData;
use App\Domain\Department\Repositories\DepartmentRepository;

class ToggleDepartmentStatus
{
    public function __construct(
        private readonly DepartmentRepository $departments
    ){}

    public function execute(int $id): void
    {
        $this->departments->toggleActive($id);
    }
}