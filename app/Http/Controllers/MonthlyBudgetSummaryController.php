<?php

namespace App\Http\Controllers;

use App\Domain\MonthlyBudget\Services\BudgetSummaryService;
use App\Models\MonthlyBudgetSummaryReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class MonthlyBudgetSummaryController extends Controller
{
    public function __construct(
        private readonly BudgetSummaryService $summaryService,
        private readonly \App\Application\Approval\Contracts\Approvals $approvals
    ) {}

    public function destroy($id)
    {
        MonthlyBudgetSummaryReport::find($id)?->delete();

        return redirect()->back()->with('success', 'Summary report deleted successfully!');
    }

    public function show($id)
    {
        $report = MonthlyBudgetSummaryReport::with('details')->find($id);

        if (! $report) {
            abort(404);
        }

        $approvalRequest = $this->approvals->currentRequest($report);
        $canApprove = $this->approvals->canAct($report, auth()->id());

        return view('monthly-budget.summary.detail', [
            'report' => $report,
            'approvalRequest' => $approvalRequest,
            'canApprove' => $canApprove,
        ]);
    }

    public function saveAutograph(Request $request, $id)
    {
        $report = MonthlyBudgetSummaryReport::findOrFail($id);
        $this->approvals->approve($report, auth()->id(), $request->remarks ?? '');

        return redirect()->back()->with('success', 'Report approved successfully');
    }

    public function reject(Request $request, $id)
    {
        $report = MonthlyBudgetSummaryReport::findOrFail($id);
        $this->approvals->reject($report, auth()->id(), $request->remarks ?? '');

        return redirect()->back()->with('success', 'Report rejected successfully');
    }

    public function returnForRevision(Request $request, $id)
    {
        $report = MonthlyBudgetSummaryReport::findOrFail($id);
        $this->approvals->return($report, auth()->id(), $request->reason ?? '');

        return redirect()->back()->with('success', 'Report returned for revision');
    }

    public function submit($id)
    {
        $report = MonthlyBudgetSummaryReport::with('details')->findOrFail($id);

        // Required field validation for all details
        $incompleteItems = $report->details->filter(function ($detail) {
            return empty($detail->supplier) || (float) $detail->cost_per_unit <= 0;
        });

        if ($incompleteItems->isNotEmpty()) {
            $itemNames = $incompleteItems->pluck('name')->unique()->implode(', ');

            return redirect()
                ->back()
                ->with('error', "Cannot submit: The following items are incomplete (missing supplier or cost): {$itemNames}. Please update them first.");
        }

        $this->approvals->submit($report, auth()->id(), [
            'is_moulding' => (bool) $report->is_moulding,
        ]);

        return redirect()->back()->with('success', 'Report submitted for approval successfully!');
    }

    public function cancel(Request $request, $id)
    {
        $report = MonthlyBudgetSummaryReport::findOrFail($id);
        $this->approvals->cancel($report, auth()->id(), $request->description);

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
                'items' => $items->toArray(),
            ];
        })->values()->toArray();

        $pdf = Pdf::loadView('pdf.monthly-budget-summary', [
            'report' => $report,
            'groupedDetails' => $groupedDetails,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Monthly-Budget-Summary-' . $report->doc_num . '.pdf');
    }
}
