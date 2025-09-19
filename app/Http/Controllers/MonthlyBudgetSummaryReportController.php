<?php

namespace App\Http\Controllers;

use App\Models\MonthlyBudgetReport;
use App\Models\MonthlyBudgetSummaryReport as Report;
use App\Models\MonthlyBudgetReportSummaryDetail as Detail;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonthlyBudgetSummaryReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            "month" => "required|date_format:m-Y", // Validate the month format
            "created_autograph" => "nullable|string",
        ]);

        $monthYear = $request->input("month");
        $date = Carbon::createFromFormat("m-Y", $monthYear)->startOfMonth()->toDateString();

        [$month, $year] = explode("-", $monthYear);

        $monthlyBudgetReports = MonthlyBudgetReport::with("details")
            ->whereYear("report_date", $year)
            ->whereMonth("report_date", $month)
            ->where("status", 6)
            ->get();

        // Collect all details, no need to filter by dept_no
        $allDetails = [];

        foreach ($monthlyBudgetReports as $monthlyBudgetReport) {
            foreach ($monthlyBudgetReport->details as $detail) {
                $allDetails[] = [
                    "dept_no" => $monthlyBudgetReport->dept_no,
                    "detail" => $detail,
                ];
            }
        }

        $report = Report::create([
            "report_date" => $date,
            "creator_id" => auth()->user()->id,
        ]);

        foreach ($allDetails as $detail) {
            Detail::create([
                "header_id" => $report->id,
                "name" => $detail["detail"]["name"],
                "dept_no" => $detail["dept_no"],
                "quantity" => $detail["detail"]["quantity"],
                "uom" => $detail["detail"]["uom"],
                "remark" => $detail["detail"]["remark"],
            ]);
        }

        return redirect()
            ->back()
            ->with("status", "Monthly Budget Summary Reports successfully created!");
    }

    public function destroy($id)
    {
        Report::find($id)->delete();
        return redirect()
            ->back()
            ->with("status", "Monthly Budget Summary Report successfully deleted!");
    }

    public function show($id)
    {
        $report = Report::with("details")->find($id);
        // dd($report->details->where('name', 'SELANG PU TRANSPARANT'));
        $this->updateStatus($report);

        // Prepare an array to hold grouped details
        $groupedDetails = [];
        $detailsToDelete = [];

        // Loop through each detail to group by name and dept_no
        foreach ($report->details as $detail) {
            $name = $detail->name;
            $spec = $detail->spec;
            $deptNo = $detail->dept_no;
            $uom = $detail->uom;
            $detailId = $detail->id;

            if (!isset($groupedDetails[$name])) {
                // Initialize if not exists
                $groupedDetails[$name] = [
                    "name" => $name,
                    "items" => [],
                ];
            }

            $found = false;
            foreach ($groupedDetails[$name]["items"] as &$item) {
                if ($item["dept_no"] === 363) {
                    if (
                        $item["dept_no"] === $deptNo &&
                        $item["uom"] === $uom &&
                        $item["spec"] === $spec
                    ) {
                        // If found, accumulate quantity and track ID for deletion
                        $item["quantity"] += $detail->quantity;
                        $detailsToDelete[] = $detailId;
                        $found = true;
                        break;
                    }
                } else {
                    if ($item["dept_no"] === $deptNo && $item["uom"] === $uom) {
                        // If found, accumulate quantity and track ID for deletion
                        $item["quantity"] += $detail->quantity;
                        $detailsToDelete[] = $detailId;
                        $found = true;
                        break;
                    }
                }
            }

            // If not found, add a new item
            if (!$found) {
                $groupedDetails[$name]["items"][] = [
                    "id" => $detailId,
                    "dept_no" => $deptNo,
                    "quantity" => $detail->quantity,
                    "spec" => $detail->spec,
                    "last_recorded_stock" => $detail->last_recorded_stock,
                    "usage_per_month" => $detail->usage_per_month,
                    "uom" => $uom,
                    "supplier" => $detail->supplier,
                    "cost_per_unit" => $detail->cost_per_unit,
                    "remark" => $detail->remark,
                    // Add other fields as needed
                ];
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
        $monthName = $reportDate->format("F"); // Full month name
        $year = $reportDate->format("Y"); // Year
        $monthYear = $monthName . " " . $year;

        $dateString = $report->created_at;
        $carbonDate = Carbon::parse($dateString);
        $formattedCreatedAt = $carbonDate->format("d/m/Y (H:i:s)");

        return view(
            "monthly_budget_report.summary.detail",
            compact("groupedDetailsForView", "report", "monthYear", "formattedCreatedAt"),
        );
    }

    public function saveAutograph(Request $request, $id)
    {
        $report = Report::find($id);
        $report->update($request->all());
        $this->updateStatus($report);

        return redirect()
            ->back()
            ->with("status", "Monthly Budget Summary Report successfully approved!");
    }

    public function reject(Request $request, $id)
    {
        Report::find($id)->update([
            "reject_reason" => $request->description,
            "is_reject" => 1,
            "status" => 6,
        ]);

        return redirect()->back()->with("success", "Monthly Budget Report successfully rejected!");
    }

    private function updateStatus($report)
    {
        if ($report->is_reject == 1) {
            $report->status = 6;
        } elseif ($report->approved_autograph) {
            $report->status = 5;
        } elseif ($report->dept_head_moulding_autograph || $report->is_known_autograph) {
            $report->status = 4;
        } elseif ($report->created_autograph) {
            if ($report->is_moulding) {
                $report->status = 3;
            } else {
                $report->status = 2;
            }
        }

        $report->save();
    }

    public function cancel(Request $request, $id)
    {
        Report::find($id)->update([
            "is_cancel" => 1,
            "cancel_reason" => $request->description,
            "status" => 7,
        ]);
        return redirect()->back()->with("success", "Monthly Budget Report successfully cancelled!");
    }
}
