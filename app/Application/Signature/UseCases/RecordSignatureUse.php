<?php

declare(strict_types=1);

namespace App\Application\Signature\UseCases;

use App\Domain\Signature\Repositories\UserSignatureRepository;

final class RecordSignatureUse
{
    public function __construct(private readonly UserSignatureRepository $repo) {}

    /**
     * Store an immutable 'used' event.
     * Example $context:
     * [
     *   'document_id' => 'INV-2025-001',
     *   'doc_sha_before' => 'abc..',
     *   'doc_sha_after'  => 'def..',
     *   'page' => 2, 'x' => 120, 'y' => 340, 'width' => 180,
     *   'ip' => '1.2.3.4', 'ua' => 'Mozilla/5.0'
     * ]
     */
    public function handle(int $userId, int $signatureId, array $context): void
    {
        $sig = $this->repo->findById($signatureId);
        if (! $sig || $sig->userId !== $userId) {
            throw new \RuntimeException('Signature not found.');
        }
        if ($sig->revokedAt !== null) {
            throw new \RuntimeException('Signature revoked.');
        }

        $this->repo->recordEvent($signatureId, 'used', $context);
    }
}
