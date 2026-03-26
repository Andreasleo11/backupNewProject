<?php

declare(strict_types=1);

namespace App\Application\Signature\Services;

use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use Illuminate\Support\Facades\DB;
use App\Domain\Signature\Entities\UserSignature;

final class BackfillUserSignaturesService
{
    /**
     * Backfill a user's new signature into all their past approval steps that lack a verified signature.
     */
    public function backfill(int $userId, UserSignature $signature): int
    {
        return DB::transaction(function () use ($userId, $signature) {
            // --- DEPRECATION NOTE ---
            // This backfill logic is a PARTIAL SOLUTION for the transition period (2025-2026).
            // It only targets PRs that were migrated from the legacy system.
            // Future-me: Remove this service and its call in CreateSignature once 
            // all active users have onboarded their first signature.
            return ApprovalStep::query()
                ->where('acted_by', $userId)
                ->whereNull('user_signature_id')
                ->where('remarks', 'Migrated from legacy signatures table') // Only migrated records
                ->update([
                    'user_signature_id'    => $signature->id,
                    'signature_image_path' => $signature->filePath,
                    'signature_sha256'     => $signature->sha256,
                ]);
        });
    }
}
