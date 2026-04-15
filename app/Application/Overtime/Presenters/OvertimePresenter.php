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
            'APPROVED' => ['label' => 'Fully Approved',        'classes' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'icon' => 'bx-check-circle'],
            'REJECTED' => ['label' => 'Rejected',              'classes' => 'bg-rose-100 text-rose-800 border-rose-200',         'icon' => 'bx-x-circle'],
            'IN_REVIEW' => ['label' => 'In Review',             'classes' => 'bg-amber-100 text-amber-800 border-amber-200',       'icon' => 'bx-time-five'],
            'SUBMITTED' => ['label' => 'Submitted',            'classes' => 'bg-sky-100 text-sky-800 border-sky-200',             'icon' => 'bx-paper-plane'],
            'RETURNED' => ['label' => 'Returned',             'classes' => 'bg-orange-100 text-orange-800 border-orange-200',    'icon' => 'bx-undo'],
            'DRAFT' => ['label' => 'Draft',                'classes' => 'bg-slate-100 text-slate-700 border-slate-200',       'icon' => 'bx-edit'],
            'CANCELED' => ['label' => 'Canceled',             'classes' => 'bg-slate-200 text-slate-500 border-slate-300',       'icon' => 'bx-comment-minus'],
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
        $meta['total_steps'] = $steps->count();
        $meta['signed_steps'] = $steps->filter(fn ($s) => strtolower($s->status ?? '') === 'approved')->count();
        $meta['current_actor'] = $fot->workflow_step;

        return $meta;
    }
}
