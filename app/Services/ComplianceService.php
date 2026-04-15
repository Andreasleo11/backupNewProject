<?php

// app/Services/ComplianceService.php

namespace App\Services;

use App\Models\Requirement;
use App\Models\RequirementAssignment;
use App\Models\RequirementUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ComplianceService
{
    /**
     * Get per-requirement status for a scope (e.g., Department model instance)
     */
    public function getScopeCompliance(object $scope): Collection
    {
        $assignments = RequirementAssignment::with('requirement')
            ->whereMorphedTo('scope', $scope)
            ->get();

        $requirementIds = $assignments->pluck('requirement_id');

        // Fetch all uploads for these requirements in one query (N+1 optimization)
        $allUploads = RequirementUpload::whereIn('requirement_id', $requirementIds)
            ->whereMorphedTo('scope', $scope)
            ->latest()
            ->get()
            ->groupBy('requirement_id');

        $today = Carbon::today();

        return $assignments->map(function ($a) use ($today, $allUploads) {
            /** @var Requirement $req */
            $req = $a->requirement;

            // Pull uploads from memory collection
            $uploads = $allUploads->get($req->id, collect());

            // filter valid uploads
            $validUploads = $uploads->filter(function ($u) use ($req, $today) {
                if ($req->requires_approval && $u->status !== 'approved') {
                    return false;
                }
                if ($u->valid_from && $today->lt($u->valid_from)) {
                    return false;
                }
                // validity_days fallback if valid_until not set
                $validUntil = $u->valid_until ?? ($u->valid_from && $req->validity_days ? $u->valid_from->copy()->addDays($req->validity_days) : null);
                if ($validUntil && $today->gt($validUntil)) {
                    return false;
                }

                return true;
            });

            $hasEnough = $validUploads->count() >= ($req->min_count ?? 1);

            return [
                'requirement' => $req,
                'assignment' => $a,
                'uploads' => $uploads,
                'valid_count' => $validUploads->count(),
                'status' => $hasEnough ? 'OK' : 'MISSING', // you can add EXPIRED, PENDING, REJECTED if needed
            ];
        });
    }

    /** Overall % compliance (mandatory only) */
    public function getScopeCompliancePercent(object $scope): int
    {
        $rows = $this->getScopeCompliance($scope);
        $mandatory = $rows->where(fn ($r) => $r['assignment']->is_mandatory);
        $total = $mandatory->count();
        if ($total === 0) {
            return 0; // Return 0% when no requirements exist instead of 100%
        }
        $ok = $mandatory->where('status', 'OK')->count();

        return (int) round(($ok / $total) * 100);
    }

    /**
     * Count how many unique requirements are assigned to a given scope.
     * Works for Department or any other model you use as a scope.
     */
    public function getScopeAssignedRequirementsCount(Model $scope): int
    {
        return RequirementAssignment::query()
            ->where('scope_type', $scope::class)
            ->where('scope_id', $scope->getKey())
            ->distinct('requirement_id')
            ->count('requirement_id');
    }
}
