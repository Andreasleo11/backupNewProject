<?php

declare(strict_types=1);

namespace App\Domain\MonthlyBudget\Services;

use App\Models\MonthlyBudgetReport;

final class BudgetApprovalService
{
    /**
     * Save autograph and update status.
     */
    public function approve(int $reportId, array $autographData): array
    {
        $report = MonthlyBudgetReport::with('department', 'user')->find($reportId);

        if (! $report) {
            return [
                'success' => false,
                'message' => 'Report not found',
            ];
        }

        $report->update($autographData);
        $this->updateStatus($report);

        return [
            'success' => true,
            'message' => 'Monthly Budget Report successfully approved',
        ];
    }

    /**
     * Reject report with reason.
     */
    public function reject(int $reportId, string $reason): array
    {
        $report = MonthlyBudgetReport::find($reportId);

        if (! $report) {
            return [
                'success' => false,
                'message' => 'Report not found',
            ];
        }

        $report->update([
            'reject_reason' => $reason,
            'is_reject' => 1,
        ]);

        return [
            'success' => true,
            'message' => 'Monthly Budget Report successfully rejected',
        ];
    }

    /**
     * Cancel report with reason.
     */
    public function cancel(int $reportId, string $reason): array
    {
        $report = MonthlyBudgetReport::find($reportId);

        if (! $report) {
            return [
                'success' => false,
                'message' => 'Report not found',
            ];
        }

        $report->update([
            'is_cancel' => true,
            'cancel_reason' => $reason,
            'status' => 5,
        ]);

        return [
            'success' => true,
            'message' => 'Monthly Budget Report successfully cancelled',
        ];
    }

    /**
     * Update report status based on approval workflow.
     */
    public function updateStatus(MonthlyBudgetReport $report): void
    {
        if ($report->is_reject === 1) {
            $report->status = 7;
        } elseif ($report->approved_autograph) {
            $report->status = 6;
        } elseif ($report->is_known_autograph) {
            if ($report->department->name === 'MOULDING') {
                $report->status = 3;
            } elseif ($report->department->name === 'QA' || $report->department->name === 'QC') {
                $report->status = 5;
            } else {
                $report->status = 4;
            }
        } elseif ($report->created_autograph) {
            $report->status = 2;
            if ($report->department->name === 'PLASTIC INJECTION') {
                $report->status = 4;
            }
        }

        $report->save();
    }

    /**
     * Get filtered reports based on user role.
     */
    public function getFilteredReportsQuery($user)
    {
        $isHead = $user->is_head === 1;
        $isGm = $user->is_gm === 1;
        $isDirector = $user->hasRole('head-management');

        $query = MonthlyBudgetReport::with('department', 'details');

        if ($user->email == 'nur@daijo.co.id') {
            return $query->whereNotNull('created_autograph');
        }

        if ($isDirector) {
            return $query->whereNotNull('created_autograph')
                ->whereNotNull('is_known_autograph')
                ->whereHas('department', function ($q) {
                    $q->where('name', 'QA')->orWhere('name', 'QC');
                });
        }

        if ($isGm) {
            return $query->whereNotNull('created_autograph')
                ->whereNotNull('is_known_autograph')
                ->whereHas('department', function ($q) {
                    $q->whereNot(function ($subQ) {
                        $subQ->where('name', 'QA')
                            ->orWhere('name', 'QC')
                            ->orWhere('name', 'MOULDING');
                    });
                });
        }

        if ($isHead) {
            $query->whereNotNull('created_autograph');
        }

        // Filter by department
        if (! ($isDirector || $isGm || $user->email === 'nur@daijo.co.id' || $user->hasRole('super-admin'))) {
            $query->whereHas('department', function ($q) use ($user) {
                $q->where(function ($subQ) use ($user) {
                    $subQ->where('id', $user->department->id);
                    if ($user->department->name === 'QA') {
                        $subQ->orWhere('name', 'QC');
                    }
                });
            });

            if ($isHead && $user->department->name === 'LOGISTIC') {
                $query->orWhere(function ($q) {
                    $q->whereHas('department', fn ($deptQ) => $deptQ->where('name', 'STORE'));
                });
            }
        }

        return $query;
    }
}
