<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\UserData;
use App\Domain\Employee\Repositories\EmployeeRepository;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepository;
use App\Domain\User\ValueObjects\Email;

class CreateUser
{
    public function __construct(
        private UserRepository $users,
        private EmployeeRepository $employees,
    ) {}

    public function execute(UserData $data): User
    {
        if ($this->users->findByEmail($data->email)) {
            throw new \DomainException('Email already in use');
        }

        if ($data->employeeId === null) {
            throw new \DomainException('Employee is required for user creation');
        }

        $employee = $this->employees->findById($data->employeeId);

        if (! $employee) {
            throw new \DomainException('Employee not found');
        }

        $existingEmployee = $this->users->findByEmployeeId($data->employeeId);
        if($existingEmployee) {
            throw new \DomainException('This employee already has a user account.');
        }
        
        if($data->password === null && $data->password === '') {
            throw new \DomainException('Password is required when creating a user.');
        }

        $user = new User(
            id: null,
            name: $data->name,
            email: new Email($data->email),
            active: $data->active,
            roles: $data->roles,
            employeeId: $data->employeeId,
        );

        $created = $this->users->create($user, $data->password ?? '');

        $this->users->setRoles($created, $data->roles);

        return $created;
    }
}
