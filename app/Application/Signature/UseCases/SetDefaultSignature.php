<?php

declare(strict_types=1);

namespace App\Application\Signature\UseCases;

use App\Domain\Signature\Repositories\UserSignatureRepository;

final class SetDefaultSignature
{
    public function __construct(private readonly UserSignatureRepository $repo) {}

    /**
     * @throws \RuntimeException if signature not found or not owned by user
     */
    public function handle(int $userId, int $signatureId): void
    {
        $sig = $this->repo->findById($signatureId);
        if (! $sig || $sig->userId !== $userId) {
            throw new \RuntimeException('Signature not found.');
        }
        if ($sig->revokedAt !== null) {
            throw new \RuntimeException('Signature already revoked.');
        }

        $this->repo->unsetDefaultForUser($userId);
        $this->repo->setDefault($signatureId);
        $this->repo->recordEvent($signatureId, 'set_default');
    }
}
