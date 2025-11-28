<?php
namespace App\Application\User\UseCases;

use App\Domain\User\Repositories\UserRepository;
use DomainException;

class ChangeUserPassword
{
    public function __construct(
        private UserRepository $users,
    ) {}

    public function execute(int $userId, string $newPassword): void
    {
        $user = $this->users->findById($userId);

        if(!$user) {
            throw new DomainException('User not Found');
        }

        $this->users->changeUserPassword($userId, $newPassword);
    }
}