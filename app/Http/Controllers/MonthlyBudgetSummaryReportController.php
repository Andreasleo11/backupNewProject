<?php

namespace App\Http\Controllers;

use App\Domain\MonthlyBudget\Services\BudgetSummaryService;
use App\Models\MonthlyBudgetSummaryReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class MonthlyBudgetSummaryReportController extends Controller
{
    public function __construct(
        private readonly BudgetSummaryService $summaryService,
        private readonly \App\Application\Approval\Contracts\Approvals $approvals
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

        if ($result['success'] && isset($result['report'])) {
            $this->approvals->submit($result['report'], (int)$request->creator_id);
        }

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

        if (!$report) {
            abort(404);
        }

        $approvalRequest = $this->approvals->currentRequest($report);
        $canApprove = $this->approvals->canAct($report, auth()->id());

        return view('monthly-budget-summary-reports.detail', [
            'report' => $report,
            'approvalRequest' => $approvalRequest,
            'canApprove' => $canApprove,
        ]);
    }

    public function saveAutograph(Request $request, $id)
    {
        $report = MonthlyBudgetSummaryReport::findOrFail($id);
        $this->approvals->approve($report, auth()->id(), $request->remark ?? '');

        return redirect()->back()->with('success', 'Report approved successfully');
    }

    public function reject(Request $request, $id)
    {
        $report = MonthlyBudgetSummaryReport::findOrFail($id);
        $this->approvals->reject($report, auth()->id(), $request->description ?? '');

        return redirect()->back()->with('success', 'Report rejected successfully');
    }

    public function cancel(Request $request, $id)
    {
        $report = MonthlyBudgetSummaryReport::findOrFail($id);
        $report->update(['is_cancel' => true, 'cancel_reason' => $request->description]);

        return redirect()->back()->with('success', 'Report cancelled successfully');
    }

    public function refresh($id)
    {
        $result = $this->summaryService->refreshSummary($id);

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function exportToPdf($id)
    {
        $report = MonthlyBudgetSummaryReport::with('details', 'department', 'user')->findOrFail($id);
        
        // Use the same grouping logic as in the show method or view
        $groupedDetails = collect($report->details)->groupBy('name')->map(function ($items, $name) {
            return [
                'name' => $name,
                'items' => $items->toArray()
            ];
        })->values()->toArray();

        $pdf = Pdf::loadView('pdf.monthly-budget-summary', [
            'report' => $report,
            'groupedDetails' => $groupedDetails,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Monthly-Budget-Summary-' . $report->doc_num . '.pdf');
    }
}
