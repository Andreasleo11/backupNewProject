<?php

declare(strict_types=1);

namespace App\Domain\MonthlyBudget\Actions;

use App\Application\Approval\Contracts\Approvals;
use App\Models\MonthlyBudgetReport;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

final class SubmitBudgetReportAction
{
    public function __construct(
        private readonly Approvals $approvals
    ) {}

    /**
     * Submit a budget report to the approval workflow.
     *
     * @param MonthlyBudgetReport $report
     * @param int $userId
     * @return array
     */
    public function execute(MonthlyBudgetReport $report, int $userId): array
    {
        try {
            // Check if already submitted or has active workflow
            if ($report->approvalRequest && $report->approvalRequest->status !== 'REJECTED') {
                return [
                    'success' => false,
                    'message' => 'Report is already submitted or in review.',
                ];
            }

            return DB::transaction(function () use ($report, $userId) {
                // Submit to approval system
                $this->approvals->submit($report, $userId, [
                    'from_department' => $report->department?->name,
                ]);

                // Log submission
                activity()
                    ->performedOn($report)
                    ->causedBy($userId)
                    ->log('Monthly Budget Report submitted for approval.');

                return [
                    'success' => true,
                    'message' => 'Report submitted for approval successfully.',
                ];
            });
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error submitting report: ' . $e->getMessage(),
            ];
        }
    }
}
