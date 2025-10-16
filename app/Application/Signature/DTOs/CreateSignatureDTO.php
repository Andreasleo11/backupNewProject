<?php

declare(strict_types=1);

namespace App\Application\Signature\DTOs;

use App\Domain\Signature\ValueObjects\SignatureKind;

final class CreateSignatureDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly ?string $label,
        public readonly SignatureKind $kind,
        public readonly ?string $filePath,          // where PNG saved
        public readonly ?string $svgPath,           // where SVG saved
        public readonly string $rawBytesForHash,    // raw PNG bytes for hashing
        public readonly ?array $metadata,           // ip, ua, canvas meta, etc.
        public readonly bool $setAsDefault = true,
    ) {}
}
