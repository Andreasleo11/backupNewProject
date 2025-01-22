<?php

namespace App\Http\Controllers\director;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PurchaseOrder;
use App\Models\MonthlyBudgetReport;
use App\Models\MonthlyBudgetSummaryReport;
use App\Models\PurchaseRequest;
use App\Models\Report;

class DirectorHomeController extends Controller
{
    public function index()
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

        // Flatten the data structure
        $chartData = $employees->map(function ($employee) {
            return [
                'Branch' => $employee->Branch,
                'Dept' => $employee->Dept,
                'Status' => $employee->employee_status,
            ];
        });

        return view('director.home', compact('reportCounts', 'purchaseRequestCounts', 'monthlyBudgetReportsCounts', 'monthlyBudgetSummaryReportsCounts', 'poCounts', 'chartData'));
    }
}
