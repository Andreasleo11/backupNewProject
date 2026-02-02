<?php

namespace App\Domain\PurchaseRequest\Services;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestSignature;
use Illuminate\Support\Collection;

/**
 * Service for managing Purchase Request signatures
 * Phase 1.5: Single Source - reads from approval_steps (primary), falls back to legacy systems
 */
class PurchaseRequestSignatureService
{
    /**
     * Add a signature to a purchase request
     * NOTE: Primarily for manual/special cases. Normal approvals create signatures via ApprovalEngine.
     */
    public function addSignature(
        PurchaseRequest $pr,
        string $stepCode,
        int $userId,
        string $imagePath
    ): PurchaseRequestSignature {
        return $pr->signatures()->create([
            'step_code' => $stepCode,
            'signed_by_user_id' => $userId,
            'image_path' => $imagePath,
            'signed_at' => now(),
        ]);
    }

    /**
     * Check if a purchase request has a signature for a specific step
     * Checks approval_steps first, then falls back to legacy systems
     */
    public function hasSignature(PurchaseRequest $pr, string $stepCode): bool
    {
        return $this->getAllSignatures($pr)
            ->contains('step_code', $stepCode);
    }

    /**
     * Get signature for a specific step
     */
    public function getSignature(PurchaseRequest $pr, string $stepCode): ?array
    {
        return $this->getAllSignatures($pr)
            ->firstWhere('step_code', $stepCode);
    }

    /**
     * Get all signatures for a Purchase Request from all sources
     * Priority: approval_steps > purchase_request_signatures > autographs
     */
    public function getAllSignatures(PurchaseRequest $pr): Collection
    {
        $signatures = collect();

        // 1. Primary source: approval_steps (modern approval system)
        if ($pr->approvalRequest) {
            $approvalSignatures = $pr->approvalRequest->steps()
                ->whereNotNull('acted_at')
                ->whereNotNull('signature_image_path')
                ->get()
                ->map(function ($step) use ($pr) {
                    return [
                        'step_code' => $this->mapSequenceToStepCode($step->sequence, $pr),
                        'signed_by_user_id' => $step->acted_by,
                        'image_path' => $step->signature_image_path,
                        'signed_at' => $step->acted_at,
                        'source' => 'approval_system',
                        'sequence' => $step->sequence,
                    ];
                });

            $signatures = $signatures->merge($approvalSignatures);
        }

        // 2. Fallback: purchase_request_signatures table (historical backfill data)
        $prSignatures = $pr->signatures()
            ->get()
            ->map(function ($sig) {
                return [
                    'step_code' => $sig->step_code,
                    'signed_by_user_id' => $sig->signed_by_user_id,
                    'image_path' => $sig->image_path,
                    'signed_at' => $sig->signed_at,
                    'source' => 'pr_signatures',
                    'sequence' => null,
                ];
            });

        $signatures = $signatures->merge($prSignatures);

        // 3. Legacy: autograph columns (for very old PRs)
        $autographs = $this->getAutographSignatures($pr);
        $signatures = $signatures->merge($autographs);

        // Return unique by step_code (approval system takes precedence)
        return $signatures->unique('step_code');
    }

    /**
     * Remove a signature (for rollback scenarios)
     */
    public function removeSignature(PurchaseRequest $pr, string $stepCode): bool
    {
        return $pr->signatures()
            ->where('step_code', $stepCode)
            ->delete() > 0;
    }

    /**
     * Map approval sequence to step code
     */
    private function mapSequenceToStepCode(int $sequence, PurchaseRequest $pr): string
    {
        // Basic mapping - can be enhanced based on approval rules
        return match ($sequence) {
            1 => 'DEPT_HEAD',
            2 => $pr->type === 'factory' ? 'GM' : 'VERIFICATOR',
            3 => 'DIRECTOR',
            default => "STEP_{$sequence}",
        };
    }

    /**
     * Extract signatures from legacy autograph columns
     */
    private function getAutographSignatures(PurchaseRequest $pr): Collection
    {
        $autographs = collect();

        $mapping = [
            1 => 'MAKER',
            2 => 'DEPT_HEAD',
            3 => 'VERIFICATOR',
            4 => 'DIRECTOR',
            5 => 'PURCHASER',
            6 => 'GM',
            7 => 'UNKNOWN',
        ];

        foreach ($mapping as $slot => $stepCode) {
            $imagePath = $pr->{"autograph_{$slot}"};
            $userId = $pr->{"autograph_user_{$slot}"};

            if ($imagePath) {
                $autographs->push([
                    'step_code' => $stepCode,
                    'signed_by_user_id' => $userId,
                    'image_path' => $imagePath,
                    'signed_at' => null, // No timestamp in old system
                    'source' => 'legacy_autograph',
                    'sequence' => null,
                ]);
            }
        }

        return $autographs;
    }
}
