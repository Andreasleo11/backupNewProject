<?php

namespace App\Http\Controllers;

use App\Models\MonthlyBudgetReport;
use App\Models\MonthlyBudgetSummaryReport as Report;
use App\Models\MonthlyBudgetReportSummaryDetails as Detail;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonthlyBudgetSummaryReportController extends Controller
{
    public function index()
    {
        $reports = Report::all();
        return view('monthly_budget_report.summary.index', compact('reports'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:m-Y', // Validate the month format
            'created_autograph' => 'nullable|string',
        ]);

        // Convert "06-2024" to "2024-06-01"
        $monthYear = $request->input('month');
        $date = Carbon::createFromFormat('m-Y', $monthYear)->startOfMonth()->toDateString(); // "2024-06-01"

        $report = Report::create([
            'report_date' => $date,
            'created_autograph' => $request->created_autograph,
        ]);

        $monthYear = $request->month;
        list($month, $year) = explode('-', $monthYear);

        $monthlyBudgetReports = MonthlyBudgetReport::with('details')
                        ->whereYear('report_date', $year)
                        ->whereMonth('report_date', $month)
                        ->get();
        // dd($monthlyBudgetReports);
        $monthlyBudgetReportDetails = $monthlyBudgetReports->pluck('details')->flatten();
        // Extract details with dept_no
        $detailsWithDeptNo = [];
        foreach ($monthlyBudgetReports as $monthlyBudgetReport) {
            foreach ($monthlyBudgetReport->details as $detail) {
                $detailsWithDeptNo[] = [
                    'dept_no' => $monthlyBudgetReport->dept_no,
                    'detail' => $detail
                ];
            }
        }
        // dd($detailsWithDeptNo[0]);
        foreach ($detailsWithDeptNo as $detail) {
            Detail::create([
                'header_id' => $report->id,
                'name' => $detail['detail']['name'],
                'dept_no' => $detail['dept_no'],
                'quantity' => $detail['detail']['quantity'],
                'uom' => $detail['detail']['uom'],
                'remark' => $detail['detail']['remark'],
            ]);
        }

        return redirect()->back()->with('status', 'Monthly Budget Summary Report successfully created!');
    }

    public function destroy($id)
    {
        Report::find($id)->delete();
        return redirect()->back()->with('status', 'Monthly Budget Summary Report successfully deleted!');
    }

    public function show($id)
    {
        $report = Report::with('details')->find($id);

        // Prepare an array to hold grouped details
        $groupedDetails = [];

        // Loop through each detail to group by name
        foreach ($report->details as $detail) {
            $name = $detail->name;
            $deptNo = $detail->dept_no;

            if (!isset($groupedDetails[$name])) {
                // Initialize if not exists
                $groupedDetails[$name] = [
                    'name' => $name,
                    'items' => [],
                ];
            }

            // Check if there's already a row with the same dept_no
            $found = false;
            foreach ($groupedDetails[$name]['items'] as &$item) {
                if ($item['dept_no'] === $deptNo) {
                    // If found, accumulate quantity
                    $item['quantity'] += $detail->quantity;
                    $found = true;
                    break;
                }
            }

            // If not found, add a new item
            if (!$found) {
                $groupedDetails[$name]['items'][] = [
                    'dept_no' => $deptNo,
                    'quantity' => $detail->quantity,
                    'uom' => $detail->uom,
                    'supplier' => $detail->supplier,
                    'cost_per_unit' => $detail->cost_per_unit,
                    'remark' => $detail->remark,
                    // Add other fields as needed
                ];
            }
        }

        // Transform array values to indexed array for the view
        $groupedDetails = array_values($groupedDetails);
        // dd($groupedDetails);

        // Extract the month name
        $reportDate = Carbon::parse($report->report_date);
        $monthName = $reportDate->format('F'); // Full month name
        $year = $reportDate->format('Y'); // Year
        $monthYear = $monthName . ' ' . $year;

        $dateString = $report->created_at;
        // Parse the date string into a Carbon instance
        $carbonDate = Carbon::parse($dateString);
        // Format the date as dd-mm-yyyy
        $formattedCreatedAt = $carbonDate->format('d/m/Y (H:i:s)'); // Output: dd-mm-yyyy

        return view('monthly_budget_report.summary.detail', compact('groupedDetails','report', 'monthYear', 'formattedCreatedAt'));
    }
}
