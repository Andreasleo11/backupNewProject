<?php

declare(strict_types=1);

namespace App\Application\Signature\UseCases;

use App\Domain\Signature\Repositories\UserSignatureRepository;
use DateTimeImmutable;

final class RevokeSignature
{
    public function __construct(private readonly UserSignatureRepository $repo) {}

    /**
     * Revoke a signature (soft). If it was default, caller can later pick a new default.
     *
     * @throws \RuntimeException if not found or not owned by user
     */
    public function handle(int $userId, int $signatureId, ?string $reason = null): void
    {
        $sig = $this->repo->findById($signatureId);
        if (! $sig || $sig->userId !== $userId) {
            throw new \RuntimeException('Signature not found.');
        }
        if ($sig->revokedAt !== null) {
            return; // already revoked
        }

        $now = new DateTimeImmutable('now');
        $this->repo->revoke($signatureId, $now);

        $context = $reason ? ['reason' => $reason] : null;
        $this->repo->recordEvent($signatureId, 'revoked', $context);
    }
}
