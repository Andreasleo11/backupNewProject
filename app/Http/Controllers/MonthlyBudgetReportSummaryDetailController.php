<?php

namespace App\Http\Controllers;

use App\Models\MonthlyBudgetReportSummaryDetail;
use Illuminate\Http\Request;

class MonthlyBudgetReportSummaryDetailController extends Controller
{
    public function update(Request $request, $id)
    {
        // Use preg_replace to remove everything except digits and the decimal point
        if($request->cost_per_unit){
            $formatedCost = preg_replace('/[^\d.]/', '', $request->cost_per_unit);
        }

        MonthlyBudgetReportSummaryDetail::find($id)->update([
            'supplier' => $request->supplier,
            'cost_per_unit' => $formatedCost ?? null,
            'remark' => $request->remark
        ]);
        return redirect()->back()->with('success', 'Details has been updated!');
    }
}
