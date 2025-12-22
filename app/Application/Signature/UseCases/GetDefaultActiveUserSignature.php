<?php

declare(strict_types=1);

namespace App\Application\Signature\UseCases;

use App\Domain\Signature\Entities\UserSignature;
use App\Domain\Signature\Repositories\UserSignatureRepository;

final class GetDefaultActiveUserSignature
{
    public function __construct(private UserSignatureRepository $repo) {}

    public function execute(int $userId): ?UserSignature
    {
        $list = $this->repo->listByUser($userId, onlyActive: true);
        return $list[0] ?? null;
    }
}