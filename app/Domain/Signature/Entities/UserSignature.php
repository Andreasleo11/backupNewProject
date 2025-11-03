<?php

declare(strict_types=1);

namespace App\Domain\Signature\Entities;

use App\Domain\Signature\ValueObjects\SignatureKind;
use DateTimeImmutable;

final class UserSignature
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly ?string $label,
        public readonly SignatureKind $kind,
        public readonly ?string $filePath,
        public readonly ?string $svgPath,
        public readonly string $sha256,
        public readonly bool $isDefault,
        public readonly ?array $metadata,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
        public readonly ?DateTimeImmutable $revokedAt,
    ) {}

    public function isActive(): bool
    {
        return $this->revokedAt === null;
    }

    public function withDefault(bool $isDefault): self
    {
        return new self(
            $this->id, $this->userId, $this->label, $this->kind,
            $this->filePath, $this->svgPath, $this->sha256,
            $isDefault, $this->metadata, $this->createdAt,
            $this->updatedAt, $this->revokedAt
        );
    }

    public function revokedCopy(DateTimeImmutable $ts): self
    {
        return new self(
            $this->id, $this->userId, $this->label, $this->kind,
            $this->filePath, $this->svgPath, $this->sha256,
            false, $this->metadata, $this->createdAt,
            $this->updatedAt, $ts
        );
    }
}
