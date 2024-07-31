<?php

namespace App\Http\Controllers;

use App\Models\MonthlyBudgetReport;
use App\Models\MonthlyBudgetSummaryReport as Report;
use App\Models\MonthlyBudgetReportSummaryDetail as Detail;
use App\Models\User;
use App\Notifications\MonthlyBudgetSummaryReportRequestSign;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        $monthYear = $request->input('month');
        $date = Carbon::createFromFormat('m-Y', $monthYear)->startOfMonth()->toDateString(); // "2024-06-01"

        $report = Report::create([
            'report_date' => $date,
            'creator_id' => auth()->user()->id
        ]);

        $monthYear = $request->month;
        list($month, $year) = explode('-', $monthYear);

        $monthlyBudgetReports = MonthlyBudgetReport::with('details')
            ->whereYear('report_date', $year)
            ->whereMonth('report_date', $month)
            ->get();

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
        $detailsToUpdate = []; // Store details that need to be updated
        $detailsToDelete = [];

        // Loop through each detail to group by name and dept_no
        foreach ($report->details as $detail) {
            $name = $detail->name;
            $deptNo = $detail->dept_no;
            $detailId = $detail->id;
            $uom = $detail->uom;

            if (!isset($groupedDetails[$name])) {
                // Initialize if not exists
                $groupedDetails[$name] = [
                    'name' => $name,
                    'items' => []
                ];
            }

            $found = false;
            foreach ($groupedDetails[$name]['items'] as &$item) {
                if ($item['dept_no'] === $deptNo && $item['uom'] === $uom) {
                    // If found, accumulate quantity and track ID for deletion
                    $item['quantity'] += $detail->quantity;
                    $detailsToDelete[] = $detailId;
                    $found = true;
                    break;
                }
            }

            // If not found, add a new item
            if (!$found) {
                $groupedDetails[$name]['items'][] = [
                    'id' => $detailId,
                    'dept_no' => $deptNo,
                    'quantity' => $detail->quantity,
                    'uom' => $uom,
                    'supplier' => $detail->supplier,
                    'cost_per_unit' => $detail->cost_per_unit,
                    'remark' => $detail->remark,
                    // Add other fields as needed
                ];
                $detailsToUpdate[] = $detailId; // Mark this item for updating
            }
        }

        // Update records with combined quantities
        foreach ($groupedDetails as $group) {
            foreach ($group['items'] as $item) {
                if (in_array($item['id'], $detailsToUpdate)) {
                    // Update the database record
                    $detailToUpdate = Detail::find($item['id']);
                    $detailToUpdate->quantity = $item['quantity'];
                    $detailToUpdate->save();
                }
            }
        }

        // Delete records with higher IDs
        if (!empty($detailsToDelete)) {
            Detail::destroy($detailsToDelete);
        }

        // Transform array values to indexed array for the view
        $groupedDetailsForView = array_values($groupedDetails);

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

        return view('monthly_budget_report.summary.detail', compact('groupedDetailsForView', 'report', 'monthYear', 'formattedCreatedAt'));
    }




    public function saveAutograph(Request $request, $id)
    {
        $report = Report::find($id)->update($request->all());

        $this->sendNotification($report);

        return redirect()->back()->with('status', 'Monthly Budget Summary Report successfully approved!');
    }

    private function sendNotification($report)
    {
        $detail = [
            'greeting' => 'Monthly Budget Report Notification',
            'body' => 'We waiting for your sign!',
            'actionText' => 'Click to see the detail',
            'actionURL' => env('APP_URL', 'http://116.254.114.93:2420/') . '/monthlyBudgetSummaryReport/' . $report->id,
        ];

        // $creator = User::find($report->creator_id)->notify(new MonthlyBudgetReportRequestSign($report, $detail));

        if ($report->created_autograph && !$report->is_known_autograph && !$report->approved_autograph) {
            $user = User::where('is_gm', 1)->first();
        } elseif ($report->created_autograph && $report->is_known_autograph && !$report->approved_autograph) {
            $user = User::with('specification')->whereHas('specification', function ($query) {
                $query->where('name', 'DIRECTOR');
            })->first();
        } elseif ($report->created_autograph && $report->is_known_autograph && $report->approved_autograph) {
            $user = User::where('email', 'nur@daijo.co.id')->first();
            $detail['body'] = "Monthly Budget Report signed!";

            // notify the creator if already signed all
            $report->user->notify(new MonthlyBudgetSummaryReportRequestSign($report, $detail));
        }

        if ($user) {
            try {
                $detail['userName'] = $user->name;
                $user->notify(new MonthlyBudgetSummaryReportRequestSign($report, $detail));

                return redirect()->back()->with('success', 'Notification sent successfully!');
            } catch (\Exception $e) {
                // Log the error (check laravel.log for details)
                Log::error('Error when sending the notification : ' . $e->getMessage());
                return redirect()->back()->with('error', 'Send notification failed!');
            }
        } else {
            Log::error('Error when sending the notification. User not found! : ');
            return redirect()->back()->with('error', 'Send notification failed! (User not found)');
        }
    }
}
