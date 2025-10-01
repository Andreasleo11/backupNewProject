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
        int $deptId, Month $month, ?string $prSigner, string $sortBy, string $sortDir, int $page, int $perPage
    ): array {
        $q = UnifiedExpensesQuery::detailByDepartment($deptId, $month->start(), $month->end());
        $qAgg = clone $q;
        $sumQty = (float) $qAgg->sum('quantity');
        $sumTotal = (float) $qAgg->sum('line_total');

        if ($prSigner) {
            $q->where(function ($w) use ($prSigner) {
                $w->where('source', 'monthly_budget')
                    ->orWhere(fn ($x) => $x->where('source', 'purchase_request')->where('autograph_5', $prSigner));
            });
        }
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
            'sumQty' => $sumQty,
            'sumTotal' => $sumTotal,
        ];
    }
}
