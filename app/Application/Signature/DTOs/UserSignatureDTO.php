<?php

declare(strict_types=1);

namespace App\Application\Signature\DTOs;

use App\Domain\Signature\Entities\UserSignature;

final class UserSignatureDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly ?string $label,
        public readonly string $kind,
        public readonly ?string $filePath,
        public readonly ?string $svgPath,
        public readonly string $sha256,
        public readonly bool $isDefault,
        public readonly ?array $metadata,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?string $revokedAt,
        public readonly bool $active,
    ) {}

    public static function fromEntity(UserSignature $e): self
    {
        return new self(
            id: $e->id ?? 0,
            userId: $e->userId,
            label: $e->label,
            kind: $e->kind->value,
            filePath: $e->filePath,
            svgPath: $e->svgPath,
            sha256: $e->sha256,
            isDefault: $e->isDefault,
            metadata: $e->metadata,
            createdAt: $e->createdAt->format('c'),
            updatedAt: $e->updatedAt->format('c'),
            revokedAt: $e->revokedAt?->format('c'),
            active: $e->isActive(),
        );
    }
}
