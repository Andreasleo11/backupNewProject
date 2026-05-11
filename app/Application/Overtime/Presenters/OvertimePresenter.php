<?php

namespace App\Application\Overtime\Presenters;

class OvertimePresenter
{
    /**
     * Map a raw `status` slug to human-readable label + JIT-safe Tailwind classes.
     * Always use complete pre-defined strings here so JIT compilation works.
     *
     * @return array{label: string, classes: string, icon: string}
     */
    public static function statusMeta(?string $status): array
    {
        $status = strtoupper($status ?? 'DRAFT');

        return match ($status) {
            'APPROVED' => ['label' => 'Fully Approved', 'classes' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'icon' => 'bx-check-circle'],
            'REJECTED' => ['label' => 'Rejected', 'classes' => 'bg-rose-100 text-rose-800 border-rose-200', 'icon' => 'bx-x-circle'],
            'IN_REVIEW' => ['label' => 'In Review', 'classes' => 'bg-amber-100 text-amber-800 border-amber-200', 'icon' => 'bx-time-five'],
            'SUBMITTED' => ['label' => 'Submitted', 'classes' => 'bg-sky-100 text-sky-800 border-sky-200', 'icon' => 'bx-paper-plane'],
            'RETURNED' => ['label' => 'Returned', 'classes' => 'bg-orange-100 text-orange-800 border-orange-200', 'icon' => 'bx-undo'],
            'DRAFT' => ['label' => 'Draft', 'classes' => 'bg-slate-100 text-slate-700 border-slate-200', 'icon' => 'bx-edit'],
            'CANCELED' => ['label' => 'Canceled', 'classes' => 'bg-slate-200 text-slate-500 border-slate-300', 'icon' => 'bx-comment-minus'],
            default => ['label' => ucwords(strtolower(str_replace(['-', '_'], ' ', $status))), 'classes' => 'bg-slate-100 text-slate-600 border-slate-200', 'icon' => 'bx-circle'],
        };
    }

    public static function reviewMeta($fot): array
    {
        $status = strtoupper($fot->workflow_status);

        if ($fot->is_push == 1) {
            $totalCount = (int) ($fot->details_count ?? 0);
            $processedCount = (int) ($fot->processed_count ?? 0);
            $failedSyncCount = (int) ($fot->failed_count ?? 0);

            if ($failedSyncCount > 0) {
                // Get unique failed reasons (specifically JPAYROLL errors)
                $reasons = $fot->failedDetails
                    ->pluck('reason')
                    ->unique()
                    ->filter()
                    ->values()
                    ->all();

                $reasonText = implode('; ', $reasons);

                return [
                    'label' => 'Sync Errors',
                    'classes' => 'bg-rose-100 text-rose-700 border-rose-200/50',
                    'icon' => 'bx-error-alt',
                    'reason' => $reasonText ?: 'Validation failed on payroll push.',
                ];
            }

            if ($processedCount === 0 && $totalCount > 0) {
                return [
                    'label' => 'Sync Failed',
                    'classes' => 'bg-rose-50 text-rose-600 border-rose-100',
                    'icon' => 'bx-x-circle',
                    'reason' => 'Form was pushed but no rows were successfully processed.',
                ];
            }

            if ($processedCount < $totalCount) {
                return [
                    'label' => 'Partial Sync',
                    'classes' => 'bg-amber-50 text-amber-600 border-amber-100',
                    'icon' => 'bx-list-check',
                    'reason' => "Only {$processedCount} of {$totalCount} rows were successfully synced.",
                ];
            }

            return [
                'label' => 'Synced Successfully',
                'classes' => 'bg-emerald-100 text-emerald-700 border-emerald-200/50',
                'icon' => 'bxs-check-shield',
            ];
        }

        if ($status === 'APPROVED') {
            // Check detail statuses to determine granular approval state
            $approvedCount = (int) ($fot->approved_count ?? 0);
            $rejectedCount = (int) ($fot->rejected_count ?? 0);
            $pendingCount = (int) ($fot->pending_count ?? 0);
            $totalDetails = (int) ($fot->details_count ?? 0);

            if ($totalDetails === 0) {
                return [
                    'label' => 'Awaiting Review',
                    'classes' => 'bg-amber-100 text-amber-700 border-amber-200/50',
                    'icon' => 'bx-time-five',
                ];
            }

            if ($approvedCount === $totalDetails) {
                return [
                    'label' => 'Fully Approved',
                    'classes' => 'bg-emerald-100 text-emerald-700 border-emerald-200/50',
                    'icon' => 'bx-check-double',
                ];
            }

            if ($rejectedCount === $totalDetails) {
                return [
                    'label' => 'Fully Rejected',
                    'classes' => 'bg-rose-100 text-rose-700 border-rose-200/50',
                    'icon' => 'bx-x-circle',
                ];
            }

            if ($approvedCount > 0 && ($rejectedCount > 0 || $pendingCount > 0)) {
                return [
                    'label' => 'Partially Approved',
                    'classes' => 'bg-blue-100 text-blue-700 border-blue-200/50',
                    'icon' => 'bx-check-circle',
                ];
            }

            if ($pendingCount === $totalDetails) {
                return [
                    'label' => 'Awaiting Review',
                    'classes' => 'bg-amber-100 text-amber-700 border-amber-200/50',
                    'icon' => 'bx-time-five',
                ];
            }

            // Fallback
            return [
                'label' => 'Awaiting Review',
                'classes' => 'bg-amber-100 text-amber-700 border-amber-200/50',
                'icon' => 'bx-time-five',
            ];
        }

        return [
            'label' => 'Pending Approval',
            'classes' => 'bg-slate-100 text-slate-500 border-slate-200/50',
            'icon' => 'bx-dots-horizontal-rounded',
        ];
    }

    public static function smartState($fot): array
    {
        $status = strtoupper($fot->workflow_status ?? 'DRAFT');
        $review = self::reviewMeta($fot);

        // Priority 1: Terminal Rejection
        if ($status === 'REJECTED') {
            $meta = self::statusMeta($status);
            $meta['stage'] = 'rejected';

            return $meta;
        }

        // Priority 2: Sync Failures or Success
        if (str_contains(strtolower($review['label']), 'sync') || str_contains(strtolower($review['label']), 'error')) {
            $review['stage'] = 'sync';
            if ($review['label'] === 'Synced Successfully') {
                $review['label'] = 'Finalized';
            }

            return $review;
        }

        // Priority 3: Audit (Signed but not synced)
        if ($status === 'APPROVED') {
            $review['stage'] = 'audit';
            $review['classes'] = 'bg-indigo-50 text-indigo-700 border-indigo-100';
            $review['icon'] = 'bxs-check-shield'; // Carry the shield into the badge
            $review['label'] = 'Auditing';

            return $review;
        }

        // Priority 4: Signing Journey
        $meta = self::statusMeta($status);
        $meta['stage'] = 'signing';
        $meta['label'] = 'Signing';
        $meta['icon'] = 'bx-pen';

        // Add signing metadata
        $steps = $fot->approvalRequest?->steps ?? collect();
        $currentStep = $steps->where('sequence', $fot->approvalRequest?->current_step)->first();

        $meta['total_steps'] = $steps->count();
        $meta['signed_steps'] = $steps->filter(fn ($s) => strtolower($s->status ?? '') === 'approved')->count();
        $meta['current_role'] = $currentStep?->approver_snapshot_role_slug ?? 'approver';
        $meta['current_actor'] = $fot->workflow_step;

        return $meta;
    }

    /**
     * Get consolidated status for a group/collection of overtime forms
     * Similar to smartState but for grouped views
     */
    public static function consolidatedState($forms): array
    {
        $forms = collect($forms);

        // Get unique workflow statuses
        $statuses = $forms->pluck('workflow_status')->map('strtoupper')->unique();
        $hasMixedStatuses = $statuses->count() > 1;

        // Check for pending details across all forms
        $hasPendingDetails = $forms->sum('pending_count') > 0;

        // Get sync/review status
        $syncStatuses = $forms->map(function ($form) {
            $review = self::reviewMeta($form);
            return $review['label'];
        })->unique();

        // Count granular approval states across all forms
        $fullyApprovedForms = $forms->filter(function ($form) {
            $approvedCount = (int) ($form->approved_count ?? 0);
            $totalDetails = (int) ($form->details_count ?? 0);
            return $totalDetails > 0 && $approvedCount === $totalDetails;
        })->count();

        $partiallyApprovedForms = $forms->filter(function ($form) {
            $approvedCount = (int) ($form->approved_count ?? 0);
            $rejectedCount = (int) ($form->rejected_count ?? 0);
            $pendingCount = (int) ($form->pending_count ?? 0);
            return $approvedCount > 0 && ($rejectedCount > 0 || $pendingCount > 0);
        })->count();

        $fullyRejectedForms = $forms->filter(function ($form) {
            $rejectedCount = (int) ($form->rejected_count ?? 0);
            $totalDetails = (int) ($form->details_count ?? 0);
            return $totalDetails > 0 && $rejectedCount === $totalDetails;
        })->count();

        $pendingReviewForms = $forms->filter(function ($form) {
            $pendingCount = (int) ($form->pending_count ?? 0);
            $totalDetails = (int) ($form->details_count ?? 0);
            return $totalDetails > 0 && $pendingCount === $totalDetails;
        })->count();

        // Priority 1: Mixed workflow statuses
        if ($hasMixedStatuses) {
            return [
                'label' => 'Mixed Status',
                'classes' => 'bg-amber-100 text-amber-800 border-amber-200',
                'icon' => 'bx-time-five',
                'stage' => 'mixed',
                'description' => 'Review Required',
            ];
        }

        // Priority 2: If any sync errors/failures
        if ($syncStatuses->contains('Sync Errors') || $syncStatuses->contains('Sync Failed') || $syncStatuses->contains('Partial Sync')) {
            $worstSync = $syncStatuses->filter(fn ($s) => str_contains($s, 'Sync'))->first();
            return [
                'label' => $worstSync,
                'classes' => 'bg-rose-100 text-rose-800 border-rose-200',
                'icon' => 'bx-error-alt',
                'stage' => 'sync',
                'description' => 'Sync Issues',
            ];
        }

        // Priority 3: If fully synced
        if ($syncStatuses->contains('Synced Successfully')) {
            return [
                'label' => 'All Finalized',
                'classes' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'icon' => 'bxs-check-shield',
                'stage' => 'finalized',
                'description' => 'View Details',
            ];
        }

        $commonStatus = $statuses->first();

        // Priority 4: Workflow approved - check granular detail statuses
        if ($commonStatus === 'APPROVED') {
            $totalForms = $forms->count();

            if ($fullyApprovedForms === $totalForms) {
                return [
                    'label' => 'All Fully Approved',
                    'classes' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    'icon' => 'bx-check-double',
                    'stage' => 'processed',
                    'description' => 'View Details',
                ];
            }

            if ($fullyRejectedForms === $totalForms) {
                return [
                    'label' => 'All Fully Rejected',
                    'classes' => 'bg-rose-100 text-rose-800 border-rose-200',
                    'icon' => 'bx-x-circle',
                    'stage' => 'processed',
                    'description' => 'View Details',
                ];
            }

            if ($partiallyApprovedForms > 0 || $fullyApprovedForms > 0 || $fullyRejectedForms > 0) {
                return [
                    'label' => 'Mixed Approval Status',
                    'classes' => 'bg-blue-100 text-blue-800 border-blue-200',
                    'icon' => 'bx-check-circle',
                    'stage' => 'audit',
                    'description' => 'Review Required',
                ];
            }

            if ($pendingReviewForms === $totalForms) {
                return [
                    'label' => 'All Pending Review',
                    'classes' => 'bg-amber-100 text-amber-800 border-amber-200',
                    'icon' => 'bx-time-five',
                    'stage' => 'audit',
                    'description' => 'Review Required',
                ];
            }

            // Fallback for approved status
            return [
                'label' => 'Under Review',
                'classes' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                'icon' => 'bx-time-five',
                'stage' => 'audit',
                'description' => 'Review Required',
            ];
        }

        // Priority 5: Workflow rejected
        if ($commonStatus === 'REJECTED') {
            return [
                'label' => 'All Rejected',
                'classes' => 'bg-rose-100 text-rose-800 border-rose-200',
                'icon' => 'bx-x-circle',
                'stage' => 'rejected',
                'description' => 'View Details',
            ];
        }

        // Priority 6: In signing process
        $meta = self::statusMeta($commonStatus);
        $meta['stage'] = 'signing';
        $meta['description'] = 'In Progress';

        return $meta;
    }
}
