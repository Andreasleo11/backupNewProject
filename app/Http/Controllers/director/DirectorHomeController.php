<?php

namespace App\Http\Controllers\director;

use App\DataTables\EmployeeWithEvaluationDataTable;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PurchaseOrder;
use App\Models\MonthlyBudgetReport;
use App\Models\MonthlyBudgetSummaryReport;
use App\Models\PurchaseRequest;
use App\Models\EmployeeWarningLog;
use App\Models\Report;
use Illuminate\Http\Request;

class DirectorHomeController extends Controller
{
    public function index(EmployeeWithEvaluationDataTable $dataTable, Request $request)
    {
        $reportCounts = [
            'approved' => Report::approved()->count(),
            'waiting' => Report::waitingApproval()->count(),
            'rejected' => Report::rejected()->count(),
        ];

        $purchaseRequestCounts = [
            'approved' => PurchaseRequest::approved()->count(),
            'waiting' => PurchaseRequest::waiting()->count(),
            'rejected' => PurchaseRequest::rejected()->count(),
        ];

        $monthlyBudgetReportsCounts = [
            'approved' => MonthlyBudgetReport::approvedByDirector()->count(),
            'waiting' => MonthlyBudgetReport::waiting()->count(),
            'rejected' => MonthlyBudgetReport::rejected()->count(),
        ];

        $monthlyBudgetSummaryReportsCounts = [
            'approved' => MonthlyBudgetSummaryReport::approved()->count(),
            'waiting' => MonthlyBudgetSummaryReport::waitingDirector()->count(),
            'rejected' => MonthlyBudgetSummaryReport::rejected()->count(),
        ];

        $poCounts = [
            'approved' => PurchaseOrder::approved()->count(),
            'waiting' => PurchaseOrder::waiting()->count(),
            'rejected' => PurchaseOrder::rejected()->count(),
        ];

        $employees = Employee::all();

        $chartData = $employees->map(function ($employee) {
            return [
                'Branch' => $employee->Branch,
                'Dept' => $employee->Dept,
                'Status' => $employee->employee_status,
            ];
        });

        $branch = $request->get('branch');
        $dept = $request->get('dept');
        $status = $request->get('status');

        return $dataTable->with([
                'branch' => $branch,
                'dept' =>  $dept,
                'status' => $status,
            ])
            ->render('director.home', compact('reportCounts', 'purchaseRequestCounts', 'monthlyBudgetReportsCounts', 'monthlyBudgetSummaryReportsCounts', 'poCounts', 'chartData', 'employees'));
        // return view('director.home', compact('reportCounts', 'purchaseRequestCounts', 'monthlyBudgetReportsCounts', 'monthlyBudgetSummaryReportsCounts', 'poCounts', 'chartData', 'employees'));
    }

    public function storeWarningLog(Request $request)
    {
        $request->validate([
            'NIK' => 'required',
            'warning_type' => 'required',
            'reason' => 'required',
        ]);

        EmployeeWarningLog::create($request->all());

        return redirect()->back()->with('success', 'Warning log has been created');
    }
    
}
