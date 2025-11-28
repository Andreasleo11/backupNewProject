<?php

namespace app\Application\User\UseCases;

use App\Domain\Employee\Repositories\EmployeeRepository;
use App\Domain\User\Repositories\UserRepository;

class AttachUserToEmployee
{
    public function __construct(
        private UserRepository $users,
        private EmployeeRepository $employees
    ) {}

    public function execute(int $userId, int $employeeId): void
    {
        $user = $this->users->findById($userId);
        if (! $user) {
            throw new \DomainException('User not found');
        }

        $employee = $this->employees->findById($employeeId);
        if (! $employee) {
            throw new \DomainException('Employee not found');
        }

        if ($this->users->findByEmployeeId($employeeId)) {
            throw new \DomainException('Employee already has a user');
        }

        $user->attachToEmployee($employeeId);
        $this->users->update($user);
    }
}
