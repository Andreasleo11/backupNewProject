<?php

namespace App\Infrastructure\Persistence\Laravel;

use App\Domain\Expenses\Contracts\ExpenseReadRepository;
use App\Domain\Expenses\DTO\DepartmentTotal;
use App\Domain\Expenses\DTO\ExpenseLine;
use App\Domain\Expenses\ValueObjects\Month;
use App\Infrastructure\Persistence\Laravel\Queries\UnifiedExpensesQuery;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class ExpenseReadRepositoryDb implements ExpenseReadRepository
{
    public function listPrSigners(Month $month): array
    {
        // Convert Month VO to Carbon for SQL helpers expecting Carbon
        $start = Carbon::instance($month->start());
        $end = Carbon::instance($month->end());

        return DB::table('purchase_requests as h')
            ->whereNull('h.deleted_at')
            ->where('h.status', 4)        // approved
            ->where('h.is_cancel', 0)
            ->whereBetween(
                DB::raw('DATE(COALESCE(h.approved_at, h.date_pr))'),
                [$start->toDateString(), $end->toDateString()]
            )
            ->whereNotNull('h.autograph_5')
            ->where('h.autograph_5', '<>', '')
            ->distinct()
            ->orderBy('h.autograph_5')
            ->pluck('h.autograph_5')
            ->values()
            ->all();
    }

    public function getDepartmentTotals(Month $month, ?string $prSigner): array
    {
        $q = UnifiedExpensesQuery::totalsPerDepartment($month->start(), $month->end());
        if ($prSigner) {
            $q->where(function ($w) use ($prSigner) {
                $w->where('u.source', 'monthly_budget')
                    ->orWhere(fn ($x) => $x->where('u.source', 'purchase_request')->where('u.autograph_5', $prSigner));
            });
        }

        return collect($q->get())->map(fn ($r) => new DepartmentTotal(
            deptId: (int) $r->dept_id,
            deptName: (string) $r->dept_name,
            deptNo: $r->dept_no ? (string) $r->dept_no : null,
            totalExpense: (float) $r->total_expense,
        ))->all();
    }

    public function getExpenseLinesByDepartment(
        int $deptId, Month $month, ?string $prSigner, string $sortBy, string $sortDir, int $page, int $perPage,
        ?string $search = null): array
    {
        $q = UnifiedExpensesQuery::detailByDepartment($deptId, $month->start(), $month->end());

        if ($prSigner) {
            $q->where(function ($w) use ($prSigner) {
                $w->where('source', 'monthly_budget')
                    ->orWhere(fn ($x) => $x->where('source', 'purchase_request')->where('autograph_5', $prSigner));
            });
        }

        if ($search !== null && $search !== '') {
            $term = '%'.$search.'%';
            $q->where(function ($qq) use ($term) {
                $qq->where('item_name', 'like', $term)
                    ->orWhere('source', 'like', $term)
                    ->orWhere('uom', 'like', $term)
                    ->orWhere('doc_num', 'like', $term);
            });
        }

        $sumTotal = (clone $q)->sum('line_total');

        $q->reorder()->orderBy($sortBy, $sortDir);
        $p = $q->paginate($perPage, ['*'], 'page', $page);

        $items = collect($p->items())->map(fn ($r) => new ExpenseLine(
            expenseDate: new \DateTimeImmutable($r->expense_date),
            source: (string) $r->source,
            autograph5: $r->autograph_5 ?: null,
            docId: (int) $r->doc_id,
            docNum: (string) $r->doc_num,
            itemName: (string) $r->item_name,
            uom: (string) $r->uom,
            quantity: (float) $r->quantity,
            unitPrice: (float) $r->unit_price,
            lineTotal: (float) $r->line_total,
        ))->all();

        return [
            'items' => $items,
            'total' => $p->total(),
            'page' => $p->currentPage(),
            'perPage' => $p->perPage(),
            'sumTotal' => $sumTotal,
        ];
    }

    public function getMonthlyDepartmentTotals(Month $start, Month $end, ?string $prSigner): array
    {
        // 1) Build the base builder
        $q = UnifiedExpensesQuery::monthlyTotalsPerDepartment($start->start(), $end->end());

        // 2) Apply signer filter (same logic you already use)
        if ($prSigner) {
            $q->where(function ($w) use ($prSigner) {
                $w->where('u.source', 'monthly_budget')
                    ->orWhere(function ($x) use ($prSigner) {
                        $x->where('u.source', 'purchase_request')
                            ->where('u.autograph_5', $prSigner);
                    });
            });
        }

        $rows = $q->get();

        // 3) Build ordered month axis (YYYY-MM)
        $months = [];
        $cur = \DateTimeImmutable::createFromFormat('Y-m-d', $start->start()->format('Y-m-01'));
        $endYm = $end->end()->format('Y-m');
        while ($cur->format('Y-m') <= $endYm) {
            $months[] = $cur->format('Y-m');
            $cur = $cur->modify('+1 month');
        }

        // 4) Group rows by department and align values to months array
        $byDept = [];
        foreach ($rows as $r) {
            $id = (int) $r->dept_id;
            if (! isset($byDept[$id])) {
                $byDept[$id] = [
                    'deptId' => $id,
                    'deptName' => (string) $r->dept_name,
                    'deptNo' => $r->dept_no !== null ? (string) $r->dept_no : null,
                    'series' => array_fill(0, count($months), 0.0),
                ];
            }
            $idx = array_search($r->ym, $months, true);
            if ($idx !== false) {
                $byDept[$id]['series'][$idx] = (float) $r->total_expense;
            }
        }

        return [
            'months' => $months,
            'departments' => array_values($byDept),
        ];
    }

    public function getLatestMonth(?string $prSigner = null): ?string
    {
        // Latest date among PR (approved, not canceled, has details)
        $prQ = DB::table('detail_purchase_requests as d')
            ->join('purchase_requests as h', 'h.id', '=', 'd.purchase_request_id')
            ->whereNull('d.deleted_at')
            ->whereNull('h.deleted_at')
            ->where('h.status', 4)   // approved
            ->where('h.is_cancel', 0);

        if ($prSigner) {
            $prQ->where('h.autograph_5', $prSigner);
        }

        // COALESCE(approved_at, date_pr) is the line's date in UnifiedExpenses
        $prMax = $prQ->max(DB::raw('COALESCE(h.approved_at, h.date_pr)'));

        // Latest date among Monthly Budget (approved, not rejected/canceled, has details)
        $mbMax = DB::table('monthly_budget_report_summary_details as d')
            ->join('monthly_budget_summary_reports as h', 'h.id', '=', 'd.header_id')
            ->whereNull('d.deleted_at')
            ->whereNull('h.deleted_at')
            ->where('h.is_cancel', 0)
            ->where('h.status', 5) // approved
            ->where('h.is_reject', 0)
            ->max('h.report_date');

        // Decide the later of the two
        $latest = null;
        if ($prMax && $mbMax) {
            $latest = ($prMax > $mbMax) ? $prMax : $mbMax;
        } elseif ($prMax) {
            $latest = $prMax;
        } elseif ($mbMax) {
            $latest = $mbMax;
        } else {
            return null;
        }

        // Return as 'YYYY-MM'
        return \Illuminate\Support\Carbon::parse($latest)->format('Y-m');
    }

    public function listMonths(?string $prSigner = null, int $limit = 24): array
    {
        // PURCHASE REQUEST months (approved, not canceled/deleted)
        $pr = DB::table('detail_purchase_requests as d')
            ->join('purchase_requests as h', 'h.id', '=', 'd.purchase_request_id')
            ->selectRaw("DATE_FORMAT(COALESCE(h.approved_at, h.date_pr), '%Y-%m') as ym")
            ->whereNull('d.deleted_at')
            ->whereNull('h.deleted_at')
            ->where('h.status', 4)
            ->where('h.is_cancel', 0);

        if ($prSigner !== null && $prSigner !== '') {
            $pr->where('h.autograph_5', $prSigner);
        }

        // MONTHLY BUDGET months (approved, not canceled/rejected/deleted)
        $mb = DB::table('monthly_budget_report_summary_details as d')
            ->join('monthly_budget_summary_reports as h', 'h.id', '=', 'd.header_id')
            ->selectRaw("DATE_FORMAT(h.report_date, '%Y-%m') as ym")
            ->whereNull('d.deleted_at')
            ->whereNull('h.deleted_at')
            ->where('h.is_cancel', 0)
            ->where('h.status', 5)
            ->where('h.is_reject', 0);

        // union -> distinct month strings -> newest first -> limit
        $union = $pr->union($mb);

        $rows = DB::query()
            ->fromSub($union, 'm')
            ->select('m.ym')
            ->whereNotNull('m.ym')
            ->groupBy('m.ym')
            ->orderBy('m.ym', 'desc')
            ->limit($limit)
            ->get();

        return array_values(array_map(fn ($r) => (string) $r->ym, $rows->all()));
    }
}
