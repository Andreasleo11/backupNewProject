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
            // Allow resubmission if not submitted, REJECTED, or RETURNED
            $allowedStatuses = ['REJECTED', 'RETURNED'];
            if ($report->approvalRequest && !in_array($report->approvalRequest->status, $allowedStatuses)) {
                return [
                    'success' => false,
                    'message' => 'Report is already submitted or in review.',
                ];
            }

            return DB::transaction(function () use ($report, $userId) {
                // Submit to approval system
                $this->approvals->submit($report, $userId, [
                    'from_department' => $report->department?->name,
                    'is_moulding' => $report->dept_no == '363',
                ]);

                // Log submission
                activity()
                    ->performedOn($report)
                    ->causedBy($userId)
                    ->log('Monthly Budget Report submitted for approval.');

                return [
                    'success' => true,
                    'message' => 'Monthly Budget Report has been signed and submitted for approval.',
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
