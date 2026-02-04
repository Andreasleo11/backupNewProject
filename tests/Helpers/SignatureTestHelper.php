<?php

namespace Tests\Helpers;

use App\Domain\Signature\Repositories\UserSignatureRepository;
use App\Domain\Signature\ValueObjects\SignatureKind;

/**
 * Signature Test Helper
 * 
 * Helper functions for creating user signatures in tests.
 */
class SignatureTestHelper
{
    /**
     * Create a default active signature for a user
     */
    public static function createDefaultSignature(int $userId): void
    {
        $repo = app(UserSignatureRepository::class);

        // Create simple SVG signature
        $svgData = '<svg width="200" height="100" xmlns="http://www.w3.org/2000/svg">' .
                   '<text x="10" y="50" font-family="cursive" font-size="24">Test Signature</text>' .
                   '</svg>';

        $repo->create(
            userId: $userId,
            label: 'Test Signature',
            kind: SignatureKind::SVG,
            filePath: null,
            svgPath: $svgData,
            sha256: hash('sha256', $svgData),
            isDefault: true,
            metadata: null
        );
    }

    /**
     * Create signatures for multiple users
     */
    public static function createSignaturesForUsers(array $userIds): void
    {
        foreach ($userIds as $userId) {
            self::createDefaultSignature($userId);
        }
    }
}
