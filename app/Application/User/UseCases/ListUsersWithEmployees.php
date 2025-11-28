<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\UserFilter;
use App\Application\User\DTOs\UserWithEmployeeSummary;
use App\Domain\Employee\Repositories\EmployeeRepository;
use App\Domain\User\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListUsersWithEmployees
{
    public function __construct(
        private UserRepository $users,
        private EmployeeRepository $employees,
    ) {}

    /**
     * @return LengthAwarePaginator<UserWithEmployeeSummary>
     */
    public function execute(UserFilter $filter): LengthAwarePaginator
    {
        // 1) Get normal user paginator (domain Users)
        $paginator = $this->users->paginate(
            perPage: $filter->perPage ?? 10,
            search: $filter->search,
            onlyActive: $filter->onlyActive,
        );

        $users = $paginator->getCollection();

        // 2) Collect all employeeIds
        $employeeIds = $users
            ->map(fn ($user) => $user->employeeId())
            ->filter() // remove null
            ->unique()
            ->values()
            ->all();

        // 3) Fetch all employees in one call
        $employees = $this->employees->findByIds($employeeIds);

        // 4) Index employees by id for quick lookup
        $employeeMap = [];
        foreach ($employees as $employee) {
            $employeeMap[$employee->id()] = $employee;
        }

        // 5) Map domain Users -> UserWithEmployeeSummary DTOs
        $dtoCollection = $users->map(function ($user) use ($employeeMap) {
            $employee = $user->employeeId()
                ? ($employeeMap[$user->employeeId()] ?? null)
                : null;

            return new UserWithEmployeeSummary(
                id: $user->id(),
                name: $user->name(),
                email: (string) $user->email(),
                active: $user->isActive(),
                roles: $user->roles(),
                employeeId: $user->employeeId(),
                employeeNik: $employee?->nik(),
                employeeName: $employee?->name(),
                employeeBranch: $employee?->branch(),
                employeeDeptCode: $employee?->deptCode(),
            );
        });

        // 6) Replace paginator collection with DTOs
        $paginator->setCollection($dtoCollection);

        return $paginator;
    }
}
