<?php

declare(strict_types=1);

namespace App\Domain\Signature\Repositories;

use App\Domain\Signature\Entities\UserSignature;
use App\Domain\Signature\ValueObjects\SignatureKind;
use DateTimeImmutable;

interface UserSignatureRepository
{
    public function findById(int $id): ?UserSignature;

    /** @return list<UserSignature> */
    public function listByUser(int $userId, bool $onlyActive = true): array;

    /**
     * Persist a new UserSignature; returns the freshly saved entity (with id).
     * $metadata will be stored as JSON.
     */
    public function create(
        int $userId,
        ?string $label,
        SignatureKind $kind,
        ?string $filePath,
        ?string $svgPath,
        string $sha256,
        bool $isDefault,
        ?array $metadata
    ): UserSignature;

    /** Set all signatures of the user to is_default = 0 (used before setting a new default). */
    public function unsetDefaultForUser(int $userId): void;

    /** Mark the given signature as default=1 (and others already unset). */
    public function setDefault(int $signatureId): void;

    /** Soft revoke (set revoked_at ts). */
    public function revoke(int $signatureId, DateTimeImmutable $revokedAt): void;

    /**
     * Record an immutable event row in signature_events.
     * $context will be stored as JSON (doc id, before/after hashes, coords, ip, ua, …).
     */
    public function recordEvent(
        int $signatureId,
        string $event,              // created | set_default | revoked | used
        ?array $context = null,
        ?DateTimeImmutable $at = null
    ): void;
}
