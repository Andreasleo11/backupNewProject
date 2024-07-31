<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMonthlyBudgetReportSummaryDetailRequest;
use App\Models\MonthlyBudgetReportSummaryDetail;
use Illuminate\Http\Request;

class MonthlyBudgetReportSummaryDetailController extends Controller
{
    public function update(UpdateMonthlyBudgetReportSummaryDetailRequest $request, $id)
    {
        $validatedData = $request->validated();

        // Use preg_replace to remove everything except digits and the decimal point
        if ($request->cost_per_unit) {
            $formattedCost = preg_replace('/[^\d.]/', '', $request->cost_per_unit);
            if (is_numeric($formattedCost) && $formattedCost > 0) {
                $validatedData['cost_per_unit'] = $formattedCost;
            } else {
                return redirect()->back()->withErrors(['cost_per_unit' => 'The cost per unit must be a positive number.']);
            }
        }

        MonthlyBudgetReportSummaryDetail::findOrFail($id)->update($validatedData);
        return redirect()->back()->with('success', 'Details has been updated!');
    }
}
