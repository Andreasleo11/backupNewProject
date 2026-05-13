<?php

namespace App\Application\Overtime\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;

class StatusFilter implements OvertimeFilter
{
    public function __construct(private ?string $status) {}

    public function apply(Builder $query): void
    {
        if ($this->status) {
            $status = strtoupper($this->status);
            switch ($status) {
                case 'PENDING':
                    // To be "Awaiting Review", the form must be fully signed (APPROVED)
                    // but the details must not have been reviewed yet (NULL).
                    $query->workflowApproved()->whereHas('details', fn ($q) => $q->whereNull('status'));
                    break;

                case 'FULLY_APPROVED':
                    // ALL details must be approved, and form must be workflow approved
                    $query->workflowApproved()
                        ->whereDoesntHave('details', fn ($q) => $q->where('status', '!=', 'Approved')->orWhereNull('status'));
                    break;

                case 'PARTIALLY_APPROVED':
                    // Form is workflow approved, but has mixed detail statuses (some approved, some rejected/pending)
                    $query->workflowApproved()
                        ->whereHas('details', fn ($q) => $q->where('status', 'Approved'))
                        ->whereHas('details', function ($q) {
                            $q->where('status', 'Rejected')->orWhereNull('status');
                        });
                    break;

                case 'FULLY_REJECTED':
                    // ALL details must be rejected, and form is workflow approved (ready for detail review)
                    $query->workflowApproved()
                        ->whereDoesntHave('details', fn ($q) => $q->where('status', '!=', 'Rejected')->orWhereNull('status'));
                    break;

                default:
                    // Fallback to original logic for approved/rejected
                    $query->whereHas('details', function ($q) use ($status) {
                        $q->where('status', ucfirst(strtolower($status)));
                    });
                    break;
            }
        }
    }
}
