<?php

namespace App\Application\User\UseCases;

use App\Domain\User\Repositories\UserRepository;
use DomainException;

class ToggleUserStatus
{
    public function __construct(
        private UserRepository $users
    ) {}

    public function execute(int $userId)
    {
        $user = $this->users->findById($userId);

        if (! $user) {
            throw new DomainException('User not found');
        }

        // Toggle in domain
        if ($user->isActive()) {
            $user->deactivate();
        } else {
            $user->activate();
        }

        // Persist changes
        $updated = $this->users->update($user);

        return $updated;
    }
}
