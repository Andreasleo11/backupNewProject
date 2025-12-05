<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\UserData;
use App\Domain\Employee\Repositories\EmployeeRepository;
use App\Domain\User\Repositories\UserRepository;
use App\Domain\User\ValueObjects\Email;
use DomainException;

class UpdateUser
{
    public function __construct(
        private UserRepository $users,
        private EmployeeRepository $employees,
    ) {}

    public function execute(int $id, UserData $data)
    {
        $existing = $this->users->findById($id);

        if (! $existing) {
            throw new DomainException('User not found');
        }

        if ((string) $existing->email() !== $data->email) {
            $existing = $this->users->findByEmail($data->email);
            
            if ($existing && $existing->id() !== $existing->id()) {
                throw new DomainException('Email already in use');
            }

            $existing->changeEmail(new Email($data->email));
        }

        $existing->rename($data->name);
        $existing->setRoles($data->roles);

        if ($data->active) {
            $existing->activate();
        } else {
            $existing->deactivate();
        }

        $employeeId = $existing->employeeId();

        if($data->employeeId !== null && $data->employeeId !== $employeeId) {
            $employee = $this->employees->findById($data->employeeId);
            if(!$employee) {
                throw new \DomainException('Employee not found');
            }

            $otherUser = $this->users->findByEmployeeId($data->employeeId);
            if($otherUser && $otherUser->id() !== $existing->id()) {
                throw new DomainException('This employee already has a user account.');
            }

            $existing->setEmployeeId($data->employeeId);
        }

        $updated = $this->users->update($existing);

        if ($data->roles) {
            $this->users->setRoles($updated, $data->roles);
        }

        return $updated;
    }
}
