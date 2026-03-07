<?php

namespace App\Domain\Evaluation\Services;

use App\Models\EvaluationData;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * EvaluationApprovalService
 *
 * All approval state transitions live here.
 * Operates on the `approval_status` column — no more nullable string pattern checking.
 *
 * Lifecycle:
 *   pending → graded → dept_approved → fully_approved
 *                    ↘ rejected (from any step)
 */
class EvaluationApprovalService
{
    /**
     * Grade a single evaluation record (saves scores + moves to 'graded').
     * Called by the grader (pengawas / supervisor).
     */
    public function grade(EvaluationData $record, array $scores, User $grader): void
    {
        $record->update(array_merge($scores, [
            'pengawas'       => $grader->name,
            'approval_status' => 'graded',
        ]));
    }

    /**
     * Dept head approves all graded records for a dept+month(+year).
     * Returns the number of records approved.
     */
    public function approveDept(
        int $month,
        int $year,
        string $deptNo,
        User $approver,
        ?string $evaluationType = null
    ): int {
        $query = $this->baseQuery($deptNo, $month, $year)
            ->where('approval_status', 'graded');

        if ($evaluationType) {
            $query->where('evaluation_type', $evaluationType);
        }

        return $query->update([
            'depthead'       => $approver->name,   // keep old column for legacy compatibility
            'approval_status' => 'dept_approved',
        ]);
    }

    /**
     * HRD/GM does final approval for all dept-approved records in a dept+month.
     * Returns the number of records approved.
     */
    public function approveHrd(
        int $month,
        int $year,
        string $deptNo,
        User $approver,
        ?string $evaluationType = null
    ): int {
        $query = $this->baseQuery($deptNo, $month, $year)
            ->where('approval_status', 'dept_approved');

        if ($evaluationType) {
            $query->where('evaluation_type', $evaluationType);
        }

        return $query->update([
            'generalmanager'  => $approver->name,  // keep old column for legacy compatibility
            'approval_status' => 'fully_approved',
        ]);
    }

    /**
     * Reject a single evaluation record (at any stage).
     * The record's approval_status returns to 'rejected' and a remark is stored.
     * The grader should re-grade to restart the flow.
     */
    public function reject(EvaluationData $record, string $remark, User $rejector): void
    {
        $record->update([
            'remark'          => $remark,
            'approval_status' => 'rejected',
            // Mark the approver field with 'rejected' for legacy compatibility
            'depthead'        => 'rejected',
        ]);
    }

    /**
     * After a rejection the grader re-grades the record.
     * This resets the status back to 'graded' so it can re-enter the approval flow.
     */
    public function regrade(EvaluationData $record, array $scores, User $grader): void
    {
        $record->update(array_merge($scores, [
            'pengawas'        => $grader->name,
            'depthead'        => null,   // clear rejection marker
            'generalmanager'  => null,
            'remark'          => null,
            'approval_status' => 'graded',
        ]));
    }

    /**
     * Whether all records for a dept+month are fully approved
     * (i.e., export is allowed).
     */
    public function canExport(int $month, int $year, string $deptNo, ?string $type = null): bool
    {
        $query = $this->baseQuery($deptNo, $month, $year);

        if ($type) {
            $query->where('evaluation_type', $type);
        }

        $total        = $query->count();
        $approved     = (clone $query)->where('approval_status', 'fully_approved')->count();

        return $total > 0 && $total === $approved;
    }

    /**
     * Get a summary count of each approval_status for a dept+month.
     *
     * Returns: ['pending' => n, 'graded' => n, 'dept_approved' => n,
     *           'fully_approved' => n, 'rejected' => n, 'total' => n]
     */
    public function statusSummary(int $month, int $year, ?string $deptNo = null, ?string $type = null): array
    {
        $query = EvaluationData::query();
        $query->whereMonth('Month', '=', $month);
        $query->whereYear('Month', '=', $year);

        if ($deptNo) {
            $query->where('dept', $deptNo);
        }

        if ($type) {
            $query->where('evaluation_type', $type);
        }

        $counts = $query
            ->select('approval_status', DB::raw('COUNT(*) as count'))
            ->groupBy('approval_status')
            ->pluck('count', 'approval_status')
            ->toArray();

        $statuses = ['pending', 'graded', 'dept_approved', 'fully_approved', 'rejected'];
        $result   = array_fill_keys($statuses, 0);

        foreach ($counts as $status => $count) {
            $result[$status] = $count;
        }

        $result['total'] = array_sum($result);

        return $result;
    }

    // ──────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────

    private function baseQuery(string $deptNo, int $month, int $year)
    {
        return EvaluationData::where('dept', $deptNo)
            ->whereMonth('Month', $month)
            ->whereYear('Month', $year);
    }
}
