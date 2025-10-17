<?php

declare(strict_types=1);

namespace App\Domain\Signature\Services;

final class SignatureHasher
{
    /**
     * Compute SHA-256 of the exact bytes you store (PNG recommended).
     * Accepts raw binary string.
     */
    public function sha256(string $bytes): string
    {
        return hash('sha256', $bytes);
    }
}
