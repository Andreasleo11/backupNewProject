<?php

namespace App\Application\Overtime\Queries;

use App\Application\Overtime\Queries\Filters\DateRangeFilter;
use App\Application\Overtime\Queries\Filters\DepartmentFilter;
use App\Application\Overtime\Queries\Filters\HideSignedFilter;
use App\Application\Overtime\Queries\Filters\OvertimeFilter;
use App\Application\Overtime\Queries\Filters\PushStatusFilter;
use App\Application\Overtime\Queries\Filters\SearchFilter;
use App\Application\Overtime\Queries\Filters\StatusFilter;
use App\Domain\Overtime\Models\OvertimeForm;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Unified entry point for all Overtime Form list queries.
 */
class OvertimeQueryBuilder
{
    public function forUser(User $user, ?Builder $query = null): Builder
    {
        $query = ($query ?? OvertimeForm::query())
            ->select([
                'header_form_overtime.id', 
                'header_form_overtime.user_id', 
                'header_form_overtime.dept_id', 
                'header_form_overtime.branch', 
                'header_form_overtime.status',
                'header_form_overtime.is_push', 
                'header_form_overtime.is_planned', 
                'header_form_overtime.is_after_hour', 
                'header_form_overtime.created_at',
            ])
            ->addSelect([
                'first_overtime_date' => \App\Domain\Overtime\Models\OvertimeFormDetail::selectRaw('MIN(start_date)')
                    ->whereColumn('header_id', 'header_form_overtime.id')
                    ->limit(1)
            ])
            ->with([
                'user:id,name',
                'department:id,name',
                'failedDetails',
                'approvalRequest.steps' => fn ($q) => $q
                    ->select(['id', 'approval_request_id', 'sequence', 'status', 'approver_snapshot_label', 'acted_by'])
                    ->orderBy('sequence'),
            ])
            ->withCount([
                'details',
                'details as approved_count' => fn ($q) => $q->where('status', 'Approved'),
                'details as rejected_count' => fn ($q) => $q->where('status', 'Rejected'),
                'details as pending_count' => fn ($q) => $q->whereNull('status'),
                'details as processed_count' => fn ($q) => $q->where('status', 'Approved')->where('is_processed', 1),
                'details as failed_count' => fn ($q) => $q->where('status', 'Rejected')->whereNotNull('reason'),
            ]);

        return $query->byRole($user);

        return $query->byRole($user);
    }

    public function withFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $filter) {
            if ($filter instanceof OvertimeFilter) {
                $filter->apply($query);
            }
        }

        return $query;
    }

    public function build(User $user, array $params = []): Builder
    {
        $query = $this->forUser($user);
        $filters = [];

        if (isset($params['startDate']) && isset($params['endDate'])) {
            $filters[] = new DateRangeFilter($params['startDate'], $params['endDate']);
        }

        if (!empty($params['dept']) && $this->isPrivilegedUser($user)) {
            $filters[] = new DepartmentFilter($params['dept']);
        }

        if (!empty($params['infoStatus']) && !($params['excludeInfoStatus'] ?? false)) {
            if ($params['infoStatus'] === 'my_approval') {
                $filters[] = new \App\Application\Overtime\Queries\Filters\MyApprovalFilter($user);
            } else {
                $filters[] = new StatusFilter($params['infoStatus']);
            }
        }

        if (!empty($params['search'])) {
            $filters[] = new SearchFilter($params['search']);
        }

        if (isset($params['hideSigned'])) {
            $filters[] = new HideSignedFilter($params['hideSigned']);
        }

        return $this->withFilters($query, $filters);
    }

    private function isPrivilegedUser(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'director', 'general-manager']) || 
               $user->can('overtime.view-all') || 
               $user->can('approval.view-all');
    }
}
