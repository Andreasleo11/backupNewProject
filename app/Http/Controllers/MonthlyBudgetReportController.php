<?php

namespace App\Http\Controllers;

use App\Domain\MonthlyBudget\Services\BudgetExportService;
use App\Domain\MonthlyBudget\Services\BudgetReportService;
use App\Models\MonthlyBudgetReport;
use Illuminate\Http\Request;

class MonthlyBudgetReportController extends Controller
{
    public function __construct(
        private readonly BudgetReportService $reportService,
        private readonly BudgetExportService $exportService,
        private readonly \App\Application\Approval\Contracts\Approvals $approvals,
        private readonly \App\Domain\MonthlyBudget\Actions\SubmitBudgetReportAction $submitAction
    ) {}

    public function show($id)
    {
        $report = MonthlyBudgetReport::with('details', 'department', 'approvalRequest.steps.actedUser')->findOrFail($id);
        
        // Use unified approval service to check if user can act
        $canApprove = $this->approvals->canAct($report, auth()->id());
        $currentApproval = $this->approvals->currentRequest($report);

        return view('monthly-budget.reports.detail', compact('report', 'canApprove', 'currentApproval'));
    }

    public function destroy($id)
    {
        $result = $this->reportService->deleteReport($id);

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function approve(Request $request, $id)
    {
        $report = MonthlyBudgetReport::findOrFail($id);
        $this->approvals->approve($report, auth()->id(), $request->remarks);

        return redirect()->back()->with('success', 'Report approved successfully.');
    }

    public function downloadExcelTemplate(Request $request)
    {
        return $this->exportService->downloadTemplate($request->dept_no);
    }

    public function reject(Request $request, $id)
    {
        $report = MonthlyBudgetReport::findOrFail($id);
        $this->approvals->reject($report, auth()->id(), $request->remarks);

        return redirect()->back()->with('success', 'Report rejected.');
    }

    public function returnForRevision(Request $request, $id)
    {
        $report = MonthlyBudgetReport::findOrFail($id);
        $this->approvals->return($report, auth()->id(), $request->reason);

        return redirect()->back()->with('success', 'Report returned for revision.');
    }

    public function cancel(Request $request, $id)
    {
        $result = $this->reportService->cancelReport((int)$id, $request->description);

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function submit($id)
    {
        $report = MonthlyBudgetReport::findOrFail($id);
        $result = $this->submitAction->execute($report, (int)auth()->id());

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }
}
