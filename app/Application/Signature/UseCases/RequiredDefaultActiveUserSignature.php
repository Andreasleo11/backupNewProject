<?php

declare(strict_types=1);

namespace App\Application\Signature\UseCases;

use App\Domain\Signature\Entities\UserSignature;
use RuntimeException;

final class RequireDefaultActiveUserSignature
{
    public function __construct(private GetDefaultActiveUserSignature $getDefault) {}

    public function execute(int $userId): UserSignature
    {
        $signature = $this->getDefault->execute($userId);

        if (! $signature) {
            throw new RuntimeException('No active signature found. Please create a signature and set it as default.');
        }

        if (! $signature->isDefault) {
            // if your entity exposes method instead, change accordingly
            throw new RuntimeException('No default signature set. Please set one signature as default.');
        }

        if ($signature->revokedAt) {
            throw new RuntimeException('Your default signature is revoked. Please set another signature as default.');
        }

        return $signature;
    }
}
