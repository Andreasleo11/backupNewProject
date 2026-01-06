<?php

namespace App\Application\PurchaseRequest\Services;

use App\Enums\ToDepartment;
use App\Models\PurchaseRequest;

final class PurchaseRequestStatusManager
{
    /**
     * Update purchase request status based on autograph progression
     */
    public function updateStatus(PurchaseRequest $pr): void
    {
        // If PR is Rejected, don't update status
        if ($pr->status === 5) {
            return;
        }

        // After Maker Autograph
        if ($pr->autograph_1 !== null) {
            $pr->status = 1;
        }

        // After Dept Head Autograph
        if ($pr->autograph_2 !== null) {
            $pr->status = $this->determineStatusAfterDeptHead($pr);
        }

        // After GM Autograph
        if ($pr->autograph_6 !== null) {
            $pr->status = $this->determineStatusAfterGM($pr);
        }

        // After Purchaser Autograph
        if ($pr->autograph_5 !== null) {
            $pr->status = $this->determineStatusAfterPurchaser($pr);
        }

        // After Verificator Autograph
        if ($pr->autograph_3 !== null) {
            $pr->status = $this->determineStatusAfterVerificator($pr);
        }

        // After Director Autograph
        if ($pr->autograph_4 !== null) {
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
