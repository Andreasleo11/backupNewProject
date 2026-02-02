<?php

namespace App\Application\PurchaseRequest\Services;

use App\Enums\ToDepartment;
use App\Models\PurchaseRequest;

final class PurchaseRequestStatusManager
{
    /**
     * Update purchase request status based on signature progression
     * Migrated from autograph system to signature system
     */
    public function updateStatus(
        PurchaseRequest $pr,
        \App\Domain\PurchaseRequest\Services\PurchaseRequestSignatureService $signatureService
    ): void {
        // If PR is Rejected, don't update status
        if ($pr->status === 5) {
            return;
        }

        // After Maker Signature
        if ($signatureService->hasSignature($pr, 'MAKER')) {
            $pr->status = 1;
        }

        // After Dept Head Signature
        if ($signatureService->hasSignature($pr, 'DEPT_HEAD')) {
            $pr->status = $this->determineStatusAfterDeptHead($pr);
        }

        // After GM Signature
        if ($signatureService->hasSignature($pr, 'GM')) {
            $pr->status = $this->determineStatusAfterGM($pr);
        }

        // After Purchaser Signature
        if ($signatureService->hasSignature($pr, 'PURCHASER')) {
            $pr->status = $this->determineStatusAfterPurchaser($pr);
        }

        // After Verificator Signature
        if ($signatureService->hasSignature($pr, 'VERIFICATOR')) {
            $pr->status = $this->determineStatusAfterVerificator($pr);
        }

        // After Director Signature
        if ($signatureService->hasSignature($pr, 'DIRECTOR')) {
            $pr->status = $this->determineStatusAfterDirector($pr);
        }

        $pr->save();
    }

    /**
     * Determine status after Department Head signs
     */
    private function determineStatusAfterDeptHead(PurchaseRequest $pr): int
    {
        if (
            $pr->from_department === 'MOULDING' ||
            $pr->from_department === 'QA' ||
            $pr->from_department === 'QC' ||
            $pr->type === 'office'
        ) {
            // Direct to purchaser
            return 6;
        }

        if ($pr->type === 'factory') {
            // Waiting for GM
            return 7;
        }

        return $pr->status;
    }

    /**
     * Determine status after GM signs
     */
    private function determineStatusAfterGM(PurchaseRequest $pr): int
    {
        // Waiting for purchaser
        return 6;
    }

    /**
     * Determine status after Purchaser signs
     */
    private function determineStatusAfterPurchaser(PurchaseRequest $pr): int
    {
        if (
            ($pr->to_department === ToDepartment::PURCHASING &&
                $pr->type === 'factory') ||
            $pr->to_department === ToDepartment::MAINTENANCE
        ) {
            if (
                $pr->from_department === 'COMPUTER' ||
                $pr->from_department === 'PERSONALIA'
            ) {
                // To verificator
                return 2;
            }

            // Direct to Director
            return 3;
        }

        if (
            $pr->to_department === ToDepartment::COMPUTER ||
            $pr->to_department === ToDepartment::PERSONALIA
        ) {
            // When verificator has not signed
            return 2;
        }

        return $pr->status;
    }

    /**
     * Determine status after Verificator signs
     */
    private function determineStatusAfterVerificator(PurchaseRequest $pr): int
    {
        // Status when director has not signed
        return 3;
    }

    /**
     * Determine status after Director signs
     */
    private function determineStatusAfterDirector(PurchaseRequest $pr): int
    {
        // Status when PR approved
        return 4;
    }
}
