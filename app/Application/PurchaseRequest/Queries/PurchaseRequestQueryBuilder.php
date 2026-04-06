<?php

namespace App\Application\PurchaseRequest\Queries;

use App\Application\PurchaseRequest\Queries\Filters\ApprovedThisMonthFilter;
use App\Application\PurchaseRequest\Queries\Filters\BranchFilter;
use App\Application\PurchaseRequest\Queries\Filters\DateRangeFilter;
use App\Application\PurchaseRequest\Queries\Filters\DepartmentFilter;
use App\Application\PurchaseRequest\Queries\Filters\InReviewFilter;
use App\Application\PurchaseRequest\Queries\Filters\MyApprovalFilter;
use App\Application\PurchaseRequest\Queries\Filters\PurchaseRequestFilter;
use App\Application\PurchaseRequest\Queries\Filters\StatusFilter;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\PurchaseRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Unified entry point for all Purchase Request list queries.
 *
 * Responsibilities:
 *   1. Apply role-based visibility scoping (who can see what).
 *   2. Apply composable filters driven by HTTP request parameters or explicit arrays.
 *
 * Used by:
 *   - PurchaseRequestsDataTable  (AJAX list)
 *   - GetPurchaseRequestStats    (dashboard counters)
 *   - GetPurchaseRequestList     (paginated list, if still needed)
 */
final class PurchaseRequestQueryBuilder
{
    /**
     * Base scoped query: what PRs is this user allowed to see at all?
     *
     * Includes default eager loads required by the DataTable and list views.
     * Super-admins receive an unfiltered query. All others get:
     *   - their own created PRs (identity)
     *   - PRs visible via the centralised ApprovalRequest visibility scope
     */
    public function forUser(User $user, ?Builder $query = null): Builder
    {
        $query = ($query ?? PurchaseRequest::query())
            ->with([
                'files',
                'createdBy',
                'approvalRequest' => fn ($q) => $q
                    ->select('id', 'approvable_id', 'approvable_type', 'status', 'current_step')
                    ->with('steps'),
            ]);

        if ($user->hasRole('super-admin')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            // Creator always sees their own PRs
            $q->where('user_id_create', $user->id)
              // Everything else: centralised approval visibility
              ->orWhereHas('approvalRequest', fn ($aq) => $aq->forUser($user));
        });
    }

    /**
     * Apply an ordered array of PurchaseRequestFilter instances to a query.
     * Filters are applied in the order they are given.
     */
    public function withFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $filter) {
            if ($filter instanceof PurchaseRequestFilter) {
                $filter->apply($query);
            }
        }

        return $query;
    }

    /**
     * Build a fully scoped + filtered query from an HTTP Request.
     * This is the primary entry point for the DataTable.
     */
    public function fromRequest(User $user, Request $request): Builder
    {
        $query   = $this->forUser($user);
        $filters = $this->resolveFilters($user, $request);

        return $this->withFilters($query, $filters);
    }

    /**
     * Resolve which filters to apply based on the incoming request parameters.
     * Returns an ordered array of PurchaseRequestFilter instances.
     *
     * @return PurchaseRequestFilter[]
     */
    private function resolveFilters(User $user, Request $request): array
    {
        $filters = [];

        // ── Custom UI Dropdown Filters ────────────────────────────────────────
        if ($request->filled('custom_status')) {
            $filters[] = new StatusFilter($request->input('custom_status'));
        }

        if ($request->filled('custom_department')) {
            $filters[] = new DepartmentFilter($request->input('custom_department'));
        }

        if ($request->filled('custom_date')) {
            $filters[] = DateRangeFilter::fromString($request->input('custom_date'));
        }

        if ($request->filled('branch')) {
            $filters[] = new BranchFilter($request->input('branch'));
        }

        // ── Top-Card / URL Preset Filters ─────────────────────────────────────
        if ($request->filled('filter')) {
            $preset = match ($request->input('filter')) {
                'my_approval'    => new MyApprovalFilter($user),
                'in_review'      => new InReviewFilter(),
                'approved_month' => new ApprovedThisMonthFilter(),
                default          => null,
            };

            if ($preset !== null) {
                $filters[] = $preset;
            }
        }

        return $filters;
    }
}
