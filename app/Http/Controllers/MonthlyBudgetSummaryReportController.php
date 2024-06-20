<?php

namespace App\Http\Controllers;

use App\Models\MonthlyBudgetSummaryReport as Report;
use Illuminate\Http\Request;

class MonthlyBudgetSummaryReportController extends Controller
{
    public function index()
    {
        return view('monthly_budget_report.summary.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'report_date' => 'required|date',
            'created_autograph' => 'nullable|string',
            'is_known_autograph' => 'nullable|string',
            'approved_autograph' => 'nullable|string',
        ]);

        Report::create([
            'report_date' => $request->report_date,
            'created_autograph' => $request->autograph_1,
            'is_known_autograph' => $request->autograph_2,
            'approved_autograph' => $request->autograph_3,
        ]);

        return redirect()->back()->with('status', 'Monthly Budget Summary Report successfully created!');
    }

    public function destroy($id)
    {
        Report::find($id)->delete();
        return redirect()->back()->with('status', 'Monthly Budget Summary Report successfully deleted!');
    }
}
