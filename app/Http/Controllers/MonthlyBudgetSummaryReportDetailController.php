<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MonthlyBudgetSummaryReportDetail as Detail;

class MonthlyBudgetSummaryReportDetailController extends Controller
{
    public function index()
    {
        return view('monthly_budget_report.summary.detail');
    }

    public function store(Request $request)
    {
        $request->validate([
            'header_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'dept_no' => 'required|integer',
            'quantity' => 'required|integer',
            'uom' => 'required|string|max:255',
            'supplier' => 'required|string|max:255',
            'cost_per_unit' => 'required|numeric',
            'remark' => 'nullable|string|max:255',
        ]);

        Detail::create([
            'header_id' => $request->header_id,
            'name' => $request->name,
            'dept_no' => $request->dept_no,
            'quantity' => $request->quantity,
            'uom' => $request->uom,
            'supplier' => $request->supplier,
            'cost_per_unit' => $request->cost_per_unit,
            'remark' => $request->remark,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'report_date' => 'date',
        ]);

        Detail::find($id)->update($request);
    }

    public function destroy($id)
    {
        Detail::find($id)->delete();
    }
}
