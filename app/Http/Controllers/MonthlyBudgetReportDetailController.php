<?php

namespace App\Http\Controllers;

use App\Models\MonthlyBudgetReportDetail as Detail;
use Illuminate\Http\Request;

class MonthlyBudgetReportDetailController extends Controller
{
    public function index()
    {
        return view('monthly_budget_report.detail');
    }

    public function store(Request $request)
    {
        $request->validate([
            'header_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'spec' => 'nullable|string|max:255',
            'uom' => 'required|string|max:255',
            'last_recorded_stock' => 'nullable|integer',
            'usage_per_month' => 'nullable|string|max:255',
            'quantity' => 'required|integer',
            'total' => 'required|integer',
            'remark' => 'required|string|max:255',
        ]);

        Detail::create([
            'header_id' => $request->header_id,
            'name' => $request->name,
            'spec' => $request->spec,
            'uom' => $request->uom,
            'last_recorded_stock' => $request->last_recorded_stock,
            'usage_per_month' => $request->usage_per_month,
            'quantity' => $request->quantity,
            'total' => $request->total,
            'remark' => $request->remark,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'department_no' => 'integer',
            'report_date' => 'date',
        ]);

        Detail::find($id)->update($request);
    }

    public function destroy($id)
    {
        Detail::find($id)->delete();
    }
}
