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
                'id', 'user_id', 'dept_id', 'branch', 'status',
                'is_push', 'is_planned', 'is_after_hour', 'created_at',
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

        // Precompute one consistent earliest date for sort & display
        $query->selectSub(function ($sub) {
            $sub->from('detail_form_overtime as d')
                ->selectRaw('MIN(d.start_date)')
                ->whereColumn('d.header_id', 'header_form_overtime.id');
        }, 'first_overtime_date');

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

        if (isset($params['isPush']) && $user->hasRole('verificator')) {
            $filters[] = new PushStatusFilter($params['isPush']);
        }

        if (!empty($params['infoStatus']) && !($params['excludeInfoStatus'] ?? false)) {
            $filters[] = new StatusFilter($params['infoStatus']);
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
