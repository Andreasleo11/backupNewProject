<?php

namespace App\Infrastructure\Persistence\Laravel\Queries;

use DateTimeInterface;
use Illuminate\Support\Facades\DB;

class UnifiedExpensesQuery
{
    /**
     * Unified line-items (alias 'u') with department attached.
     * Columns:
     *  - dept_id, dept_name, dept_no
     *  - expense_date, source, item_name, uom, quantity, unit_price, line_total
     */
    public static function base(DateTimeInterface $start, DateTimeInterface $end)
    {
        // ---- PURCHASE REQUEST LINES ----
        // amount = quantity * price
        // date   = approved_at (if available) else date_pr
        $prLines = DB::table('detail_purchase_requests as d')
            ->join('purchase_requests as h', 'h.id', '=', 'd.purchase_request_id')
            ->leftJoin('departments as dep', 'dep.name', '=', 'h.from_department')
            ->whereNull('d.deleted_at')
            ->whereNull('h.deleted_at')
            ->where('h.status', 4) // approved
            ->where('h.is_cancel', 0)->selectRaw("
                COALESCE(dep.id, 0)                                         as dept_id,
                COALESCE(dep.name, h.from_department, 'Unknown')            as dept_name,
                dep.dept_no                                                 as dept_no,

                CAST(COALESCE(h.approved_at, h.date_pr) AS DATE)            as expense_date,
                'purchase_request'                                          as source,

                h.id                                                        as doc_id,
                COALESCE(h.pr_no, h.doc_num, h.id)                          as doc_num,
                
                COALESCE(h.autograph_5, '')                                 as autograph_5,

                COALESCE(d.item_name, '')                                   as item_name,
                COALESCE(d.uom, 'PCS')                                      as uom,
                CAST(COALESCE(d.quantity, 0) AS DECIMAL(20,4))              as quantity,
                CAST(COALESCE(d.price, 0)    AS DECIMAL(20,4))              as unit_price,
                CAST((COALESCE(d.quantity,0) * COALESCE(d.price,0)) AS DECIMAL(20,4)) as line_total
            ");

        // ---- MONTHLY BUDGET SUMMARY LINES ----
        // amount = quantity * cost_per_unit
        // date   = report_date
        $mbLines = DB::table('monthly_budget_report_summary_details as d')
            ->join('monthly_budget_summary_reports as h', 'h.id', '=', 'd.header_id')
            // departments.dept_no is VARCHAR, details.dept_no is INT → CAST on join
            ->leftJoin('departments as dep', function ($j) {
                $j->on('dep.dept_no', '=', DB::raw('CAST(d.dept_no AS CHAR)'));
            })
            ->whereNull('d.deleted_at')
            ->whereNull('h.deleted_at')
            ->where('h.is_cancel', 0)
            ->where('h.status', 5) // approved
            ->where('h.is_reject', 0)->selectRaw("
                COALESCE(dep.id, 0)                                         as dept_id,
                COALESCE(dep.name, CONCAT('Dept ', d.dept_no))              as dept_name,
                COALESCE(dep.dept_no, CAST(d.dept_no AS CHAR))              as dept_no,

                CAST(h.report_date AS DATE)                                 as expense_date,
                'monthly_budget'                                            as source,

                h.id                                                        as doc_id,
                COALESCE(h.doc_num, h.id)                                   as doc_num,
                
                CAST(NULL AS CHAR)                                          as autograph_5,

                COALESCE(d.name, '')                                        as item_name,
                COALESCE(d.uom, 'PCS')                                      as uom,
                CAST(COALESCE(d.quantity, 0)         AS DECIMAL(20,4))      as quantity,
                CAST(COALESCE(d.cost_per_unit, 0)    AS DECIMAL(20,4))      as unit_price,
                CAST((COALESCE(d.quantity,0) * COALESCE(d.cost_per_unit,0)) AS DECIMAL(20,4)) as line_total
            ");

        $union = $prLines->unionAll($mbLines);

        return DB::query()
            ->fromSub($union, 'u')
            ->whereBetween('u.expense_date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
    }

    /** Totals per department (stable on dept_id) */
    public static function totalsPerDepartment(DateTimeInterface $start, DateTimeInterface $end)
    {
        return self::base($start, $end)
            ->selectRaw('u.dept_id, u.dept_name, u.dept_no, SUM(u.line_total) AS total_expense')
            ->groupBy('u.dept_id', 'u.dept_name', 'u.dept_no')
            ->orderBy('u.dept_name');
    }

    /** Drilldown lines for a department */
    public static function detailByDepartment(int $deptId, DateTimeInterface $start, DateTimeInterface $end)
    {
        return self::base($start, $end)
            ->where('u.dept_id', $deptId)
            ->select('u.*')
            ->orderBy('u.expense_date', 'desc');
    }
}
