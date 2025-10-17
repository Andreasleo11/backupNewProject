<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Signature\Entities\UserSignature; // adapt if your User model lives elsewhere
use App\Models\User;

class SignaturePolicy
{
    public function view(User $user, UserSignature $signature): bool
    {
        return $user->id === $signature->userId || $user->can('signatures.view.any');
    }

    public function setDefault(User $user, UserSignature $signature): bool
    {
        return $user->id === $signature->userId || $user->can('signatures.set_default.any');
    }

    public function revoke(User $user, UserSignature $signature): bool
    {
        return $user->id === $signature->userId || $user->can('signatures.revoke.any');
    }

    public function use(User $user, UserSignature $signature): bool
    {
        return $signature->revokedAt === null && ($user->id === $signature->userId || $user->can('signatures.use.any'));
    }
}
