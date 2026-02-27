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
        private readonly BudgetExportService $exportService
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

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
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
        $report = MonthlyBudgetReport::with('details', 'department')->find($id);
        $this->approvalService->updateStatus($report);

        return view('monthly-budget-reports.detail', compact('report'));
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

    public function saveAutograph(Request $request, $id)
    {
        $result = $this->approvalService->approve($id, $request->all());

        return redirect()->back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    public function downloadExcelTemplate(Request $request)
    {
        return $this->exportService->downloadTemplate($request->dept_no);
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
}
