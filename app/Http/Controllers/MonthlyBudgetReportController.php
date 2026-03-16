<?php

namespace App\Http\Controllers;

use App\Domain\MonthlyBudget\Services\BudgetApprovalService;
use App\Domain\MonthlyBudget\Services\BudgetExportService;
use App\Domain\MonthlyBudget\Services\BudgetReportService;
use App\Http\Requests\UpdateMonthlyBudgetReportRequest;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Models\MonthlyBudgetReport;
use Illuminate\Http\Request;

class MonthlyBudgetReportController extends Controller
{
    public function __construct(
        private readonly BudgetReportService $reportService,
        private readonly BudgetApprovalService $approvalService,
        private readonly BudgetExportService $exportService,
        private readonly \App\Application\Approval\Contracts\Approvals $approvals,
        private readonly \App\Domain\MonthlyBudget\Actions\SubmitBudgetReportAction $submitAction
    ) {}

    public function index()
    {
        $query = $this->approvalService->getFilteredReportsQuery(auth()->user());
        $reports = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('monthly-budget-reports.index', compact('reports'));
    }

    public function create()
    {
        $departments = Department::all();

        return view('monthly-budget-reports.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dept_no' => 'required|integer',
            'creator_id' => 'required|integer',
            'report_date' => 'required|date',
            'created_autograph' => 'nullable|string',
            'is_known_autograph' => 'nullable|string',
            'approved_autograph' => 'nullable|string',
        ]);

        if ($request->has('input_method') && $request->input_method == 'excel') {
            $request->validate(['excel_file' => 'required|file|mimes:xlsx,xls']);

            $result = $this->reportService->createFromExcel($validated, $request->file('excel_file'));
        } else {
            $request->validate([
                'items' => 'required|array',
                'items.*.name' => 'required|string|max:255',
                'items.*.spec' => 'nullable|string|max:255',
                'items.*.uom' => 'required|string|max:255',
                'items.*.last_recorded_stock' => 'nullable|integer',
                'items.*.usage_per_month' => 'nullable|string|max:255',
                'items.*.quantity' => 'required|integer',
                'items.*.remark' => 'required|string|max:255',
            ]);

            $result = $this->reportService->createReport($validated, $request->items);
        }

        if ($result['success']) {
            $report = $result['report'] ?? null;
            if ($report && $request->input('action') === 'submit') {
                $this->submitAction->execute($report, (int)auth()->id());
            }
            return redirect()->route('monthly-budget-reports.index')->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    public function edit($id)
    {
        $departments = Department::all();
        $report = MonthlyBudgetReport::find($id);
        $details = $report->details;

        return view('monthly-budget-reports.edit', compact('departments', 'report', 'details'));
    }

    public function show($id)
    {
        $report = MonthlyBudgetReport::with('details', 'department', 'approvalRequest.steps.actedUser')->findOrFail($id);
        
        // Use unified approval service to check if user can act
        $canApprove = $this->approvals->canAct($report, auth()->id());
        $currentApproval = $this->approvals->currentRequest($report);

        return view('monthly-budget-reports.detail', compact('report', 'canApprove', 'currentApproval'));
    }

    public function update(UpdateMonthlyBudgetReportRequest $request, $id)
    {
        $result = $this->reportService->updateReport($id, $request->validated());

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
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
        $this->approvals->reject($report, auth()->id(), $request->description);

        return redirect()->back()->with('success', 'Report rejected.');
    }

    public function cancel(Request $request, $id)
    {
        $result = $this->approvalService->cancel($id, $request->description);

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
