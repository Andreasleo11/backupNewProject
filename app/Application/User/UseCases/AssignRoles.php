<?php

namespace App\Application\User\UseCases;

use App\Domain\User\Repositories\UserRepository;
use DomainException;

class AssignRoles
{
    public function __construct(
        private UserRepository $users
    ) {}

    /**
     * @param string[] $roles  Role names to assign (e.g. ['admin', 'manager'])
     */
    public function execute(int $userId, array $roles)
    {
        $user = $this->users->findById($userId);

        if (! $user) {
            throw new DomainException('User not found');
        }

        // Update in domain
        $user->setRoles($roles);

        // Persist
        $updated = $this->users->update($user);

        // Sync in infra (Spatie syncRoles, etc.)
        $this->users->setRoles($updated, $roles);

        return $updated;
    }
}
