<?php

namespace App\Domain\Expenses;

use App\Domain\Expenses\Queries\UnifiedExpensesQuery;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ExpenseRepository
{
    /** Return [start, end] Carbon objects for a given "YYYY-MM" */
    private function monthRange(string $ym): array
    {
        $start = Carbon::parse($ym.'-01')->startOfMonth();
        $end = Carbon::parse($ym.'-01')->endOfMonth();

        return [$start, $end];
    }

    /** Distinct PR approvers (autograph_5) for the month */
    public function prSignersForMonth(string $ym): Collection
    {
        [$start, $end] = $this->monthRange($ym);

        return DB::table('purchase_requests as h')
            ->whereNull('h.deleted_at')
            ->where('h.status', 4)          // approved
            ->where('h.is_cancel', 0)
            ->whereBetween(DB::raw('DATE(COALESCE(h.approved_at, h.date_pr))'), [
                $start->toDateString(), $end->toDateString(),
            ])
            ->whereNotNull('h.autograph_5')
            ->where('h.autograph_5', '<>', '')
            ->distinct()
            ->orderBy('h.autograph_5')
            ->pluck('h.autograph_5');
    }

    /** Totals per department; optionally filter PR lines by approver */
    public function totalsPerDepartmentForMonth(string $ym, ?string $prSigner = null): Collection
    {
        [$start, $end] = $this->monthRange($ym);

        $q = UnifiedExpensesQuery::totalsPerDepartment($start, $end);

        if ($prSigner) {
            $q->where(function ($w) use ($prSigner) {
                $w->where('u.source', 'monthly_budget')
                    ->orWhere(function ($x) use ($prSigner) {
                        $x->where('u.source', 'purchase_request')
                            ->where('u.autograph_5', $prSigner);
                    });
            });
        }

        return $q->get();
    }

    /** Base detail query for a department; optional PR approver filter */
    public function detailQueryForMonth(int $deptId, string $ym, ?string $prSigner = null): Builder
    {
        [$start, $end] = $this->monthRange($ym);

        $q = UnifiedExpensesQuery::detailByDepartment($deptId, $start, $end);

        if ($prSigner) {
            $q->where(function ($w) use ($prSigner) {
                $w->where('source', 'monthly_budget')
                    ->orWhere(function ($x) use ($prSigner) {
                        $x->where('source', 'purchase_request')
                            ->where('autograph_5', $prSigner);
                    });
            });
        }

        return $q;
    }
}
