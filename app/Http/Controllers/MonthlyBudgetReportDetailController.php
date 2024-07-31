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
        $validatedData = $request->validate([
            'header_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'spec' => 'nullable|string|max:255',
            'uom' => 'required|string|max:255',
            'last_recorded_stock' => 'nullable|integer',
            'usage_per_month' => 'nullable|string|max:255',
            'quantity' => 'required|integer',
            'remark' => 'required|string|max:255',
        ]);

        Detail::create($validatedData);

        return redirect()->back()->with('success', 'Monthly Budget Report Detail created successfully1');
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'spec' => 'nullable|string|max:255',
            'uom' => 'required|string|max:255',
            'last_recorded_stock' => 'nullable|integer',
            'usage_per_month' => 'nullable|string|max:255',
            'quantity' => 'required|integer',
            'remark' => 'nullable|string|max:255',
        ]);

        $detail = Detail::findOrFail($id);
        $detail->update($validatedData);

        return redirect()->back()->with('success', 'Monthly Budget Report Detail updated successfully!');
    }

    public function destroy($id)
    {
        Detail::find($id)->delete();
        return redirect()->back()->with('success', 'Monthly Budget Report Detail deleted successfully!');
    }
}
