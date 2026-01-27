<?php

declare(strict_types=1);

namespace App\Domain\PurchaseRequest\Services;

use App\Models\PurchaseRequest;

/**
 * Calculates Purchase Request status based on business rules.
 *
 * Status codes:
 * 0 = Draft (not submitted)
 * 1 = Pending Department Head
 * 2 = Pending Verificator
 * 3 = Pending Director
 * 4 = Approved
 * 5 = Rejected
 * 6 = Pending Purchaser
 * 7 = Pending GM
 * 8 = Cancelled/Draft
 */
class PurchaseRequestStatusCalculator
{
    /**
     * Calculate initial status when creating a new Purchase Request.
     *
     * @param string $fromDepartment The requesting department
     * @param string $branch The branch location (JAKARTA, KARAWANG)
     * @param bool $isDraft Whether this is a draft PR
     * @return int Status code
     */
    public function calculateInitialStatus(
        string $fromDepartment,
        string $branch,
        bool $isDraft
    ): int {
        if ($isDraft) {
            return 8; // Draft status
        }

        $from = strtoupper($fromDepartment);

        // Special cases that skip normal flow
        if ($from === 'PLASTIC INJECTION') {
            return 7; // Goes directly to GM
        }

        if ($from === 'MAINTENANCE MACHINE' && strtoupper($branch) === 'KARAWANG') {
            return 7; // Goes directly to GM
        }

        // Default: Start with pending department head
        if ($from === 'PERSONALIA' || $from === 'PERSONNEL') {
            return 6; // Goes directly to purchaser
        }

        return 1;
    }

    /**
     * Calculate status based on autograph signatures.
     * This is used for legacy signature system.
     *
     * @deprecated Use approval workflow system instead
     *
     * @param PurchaseRequest $pr The purchase request
     * @return int Updated status code
     */
    public function calculateStatusFromAutographs(PurchaseRequest $pr): int
    {
        // If rejected, status doesn't change
        if ($pr->status === 5) {
            return 5;
        }

        $status = 0; // Default to draft

        // After Maker signature
        if ($pr->autograph_1 !== null) {
            $status = 1; // Pending dept head
        }

        // After Department Head signature
        if ($pr->autograph_2 !== null) {
            if (
                $pr->from_department === 'MOULDING' ||
                $pr->from_department === 'QA' ||
                $pr->from_department === 'QC' ||
                $pr->type === 'office'
            ) {
                // Office or specific depts: direct to purchaser
                $status = 6;
            } elseif ($pr->type === 'factory') {
                // Factory: needs GM approval
                $status = 7;
            }
        }

        // After GM signature
        if ($pr->autograph_6 !== null) {
            $status = 6; // Waiting for purchaser
        }

        // After Purchaser signature
        if ($pr->autograph_5 !== null) {
            $toDept = $pr->to_department?->value ?? '';

            if (
                ($toDept === 'Purchasing' && $pr->type === 'factory') ||
                $toDept === 'Maintenance'
            ) {
                if (
                    $pr->from_department === 'COMPUTER' ||
                    $pr->from_department === 'PERSONALIA'
                ) {
                    $status = 2; // To verificator
                } else {
                    $status = 3; // Direct to director
                }
            } elseif (
                $toDept === 'Computer' ||
                $toDept === 'Personnel'
            ) {
                $status = 2; // Waiting for verificator
            }
        }

        // After Verificator signature
        if ($pr->autograph_3 !== null) {
            $status = 3; // Waiting for director
        }

        // After Director signature
        if ($pr->autograph_4 !== null) {
            $status = 4; // Approved
        }

        return $status;
    }

    /**
     * Determine if a status indicates the PR is pending approval.
     *
     * @param int $status The status code
     * @return bool True if pending, false otherwise
     */
    public function isPending(int $status): bool
    {
        return in_array($status, [1, 2, 3, 6, 7], true);
    }

    /**
     * Determine if a status indicates the PR is completed.
     *
     * @param int $status The status code
     * @return bool True if completed, false otherwise
     */
    public function isCompleted(int $status): bool
    {
        return $status === 4;
    }

    /**
     * Determine if a status indicates the PR is rejected.
     *
     * @param int $status The status code
     * @return bool True if rejected, false otherwise
     */
    public function isRejected(int $status): bool
    {
        return $status === 5;
    }

    /**
     * Determine if a status indicates the PR is a draft or cancelled.
     *
     * @param int $status The status code
     * @return bool True if draft/cancelled, false otherwise
     */
    public function isDraftOrCancelled(int $status): bool
    {
        return in_array($status, [0, 8], true);
    }

    /**
     * Get human-readable status text.
     *
     * @param int $status The status code
     * @return string Status text
     */
    public function getStatusText(int $status): string
    {
        return match ($status) {
            0 => 'Draft',
            1 => 'Pending Department Head',
            2 => 'Pending Verificator',
            3 => 'Pending Director',
            4 => 'Approved',
            5 => 'Rejected',
            6 => 'Pending Purchaser',
            7 => 'Pending GM',
            8 => 'Cancelled/Draft',
            default => 'Unknown',
        };
    }
}
