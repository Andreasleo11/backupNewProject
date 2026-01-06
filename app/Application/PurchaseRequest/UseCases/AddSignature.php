<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\AddSignatureDTO;
use App\Models\PurchaseRequest;

final class AddSignature
{
    // Step-to-role mapping configuration
    private const STEP_MAP = [
        1 => 'MAKER',
        2 => 'DEPT_HEAD',
        3 => 'VERIFICATOR',
        4 => 'DIRECTOR',
        5 => 'PURCHASER',
        6 => 'GM',
        7 => 'HEAD_DESIGN',
    ];

    public function handle(AddSignatureDTO $dto): void
    {
        $pr = PurchaseRequest::findOrFail($dto->purchaseRequestId);

        $stepCode = self::STEP_MAP[$dto->section] ?? null;

        if (! $stepCode) {
            return;
        }

        // Update or create the signature
        $pr->signatures()->updateOrCreate(
            ['step_code' => $stepCode],
            [
                'signed_by_user_id' => $dto->signedByUserId,
                'image_path' => $dto->imagePath,
                'signed_at' => now(),
            ]
        );

        // If the role is DIRECTOR, mark the approval timestamp
        if ($stepCode === 'DIRECTOR') {
            $pr->update([
                'approved_at' => now(),
            ]);
        }
    }
}
