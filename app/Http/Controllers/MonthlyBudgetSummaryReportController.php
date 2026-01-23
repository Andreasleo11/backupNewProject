<?php

namespace App\Http\Controllers;

use App\Domain\MonthlyBudget\Services\BudgetApprovalService;
use App\Domain\MonthlyBudget\Services\BudgetSummaryService;
use App\Models\MonthlyBudgetSummaryReport;
use Illuminate\Http\Request;

class MonthlyBudgetSummaryReportController extends Controller
{
    public function __construct(
        private readonly BudgetSummaryService $summaryService,
        private readonly BudgetApprovalService $approvalService
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dept_no' => 'required|integer',
            'creator_id' => 'required|integer',
            'report_date' => 'required|date',
            'department_reports' => 'required|array',
        ]);

        $result = $this->summaryService->createSummary(
            $validated,
            $request->department_reports
        );

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function destroy($id)
    {
        MonthlyBudgetSummaryReport::find($id)?->delete();

        return redirect()->back()->with('success', 'Summary report deleted successfully!');
    }

    public function show($id)
    {
        $report = MonthlyBudgetSummaryReport::with('details', 'department')->find($id);

        if ($report) {
            $this->approvalService->updateStatus($report);
        }

        return view('monthly-budget-summary-reports.detail', compact('report'));
    }

    public function saveAutograph(Request $request, $id)
    {
        $result = $this->approvalService->approve($id, $request->all());

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function reject(Request $request, $id)
    {
        $result = $this->approvalService->reject($id, $request->description);

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function cancel(Request $request, $id)
    {
        $result = $this->approvalService->cancel($id, $request->description);

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function refresh($id)
    {
        $result = $this->summaryService->refreshSummary($id);

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }
}
