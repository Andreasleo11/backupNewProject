<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\Services;

use Illuminate\Support\Facades\DB;

class HistoricalPrBackfiller
{
    /**
     * Backfill workflow_status and workflow_step for historical records.
     *
     * @return int Number of records updated
     */
    public function backfill(): int
    {
        $updatedCount = 0;

        DB::table('purchase_requests')
            ->whereNull('workflow_status')
            ->orderBy('id')
            ->chunk(100, function ($prs) use (&$updatedCount) {
                foreach ($prs as $pr) {
                    $update = $this->mapStatusToWorkflow(
                        (int) $pr->status,
                        (bool) ($pr->is_cancel ?? false)
                    );
                    if ($update) {
                        DB::table('purchase_requests')
                            ->where('id', $pr->id)
                            ->update($update);
                        $updatedCount++;
                    }
                }
            });

        return $updatedCount;
    }

    /**
     * Map legacy status integer to new workflow status and step.
     */
    public function mapStatusToWorkflow(int $status, bool $isCancel = false): ?array
    {
        if ($isCancel) {
            return ['workflow_status' => 'CANCELED', 'workflow_step' => null];
        }

        return match ($status) {
            0 => ['workflow_status' => 'DRAFT', 'workflow_step' => null],
            1 => ['workflow_status' => 'IN_REVIEW', 'workflow_step' => 'Pending Dept Head'],
            2 => ['workflow_status' => 'IN_REVIEW', 'workflow_step' => 'Pending Verificator'],
            3 => ['workflow_status' => 'IN_REVIEW', 'workflow_step' => 'Pending Director'],
            4 => ['workflow_status' => 'APPROVED', 'workflow_step' => null],
            5 => ['workflow_status' => 'REJECTED', 'workflow_step' => null],
            6 => ['workflow_status' => 'IN_REVIEW', 'workflow_step' => 'Pending Purchaser'],
            7 => ['workflow_status' => 'IN_REVIEW', 'workflow_step' => 'Pending GM'],
            8 => ['workflow_status' => 'CANCELED', 'workflow_step' => null],
            default => null,
        };
    }
}
