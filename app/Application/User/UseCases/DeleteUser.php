<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\UserData;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepository;

class DeleteUser
{
    public function __construct(
        private UserRepository $users
    ) {}

    public function execute(int $id, UserData $data): User
    {
        $user = $this->users->findById($id);

        if (! $user) {
            throw new \DomainException('User not found');
        }

        $user->rename($data->name);
        $user->setRoles($data->roles);

        if ($data->active) {
            $user->activate();
        } else {
            $user->deactivate();
        }

        $updated = $this->users->update($user);

        if ($data->roles) {
            $this->users->setRoles($updated, $data->roles);
        }

        return $updated;
    }
}
