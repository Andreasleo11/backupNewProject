<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyBudgetReportTemplateExport;
use App\Http\Requests\UpdateMonthlyBudgetReportRequest;
use App\Imports\MonthlyBudgetReportImport;
use App\Models\Department;
use App\Models\MonthlyBudgetReport;
use App\Models\MonthlyBudgetReport as Report;
use App\Models\MonthlyBudgetReportDetail as Detail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class MonthlyBudgetReportController extends Controller
{
    public function index()
    {
        $authUser = auth()->user();
        $isHead = $authUser->is_head === 1;
        $isGm = $authUser->is_gm === 1;
        $isDirector = $authUser->specification->name === 'DIRECTOR';

        $reportsQuery = Report::with('department', 'details');

        if ($authUser->email == 'nur@daijo.co.id') {
            $reportsQuery = Report::whereNotNull('created_autograph');
        } elseif ($isDirector) {
            $reportsQuery = Report::whereNotNull('created_autograph')
                ->whereNotNull('is_known_autograph')
                ->whereHas('department', function ($query) {
                    $query->where('name', 'QA')->orWhere('name', 'QC');
                });
        } elseif ($isGm) {
            $reportsQuery = Report::whereNotNull('created_autograph')
                ->whereNotNull('is_known_autograph')
                ->whereHas('department', function ($query) {
                    $query->whereNot(function ($query) {
                        $query
                            ->where('name', 'QA')
                            ->orWhere('name', 'QC')
                            ->orWhere('name', 'MOULDING');
                    });
                });
        } elseif ($isHead) {
            $reportsQuery = Report::whereNotNull('created_autograph');
        }

        // filter by auth user department or if it's user create the report besides DIRECTOR and GM
        if (
            ! (
                $isDirector ||
                $isGm ||
                $authUser->email === 'nur@daijo.co.id' ||
                $authUser->role->name === 'SUPERADMIN'
            )
        ) {
            $reportsQuery->whereHas('department', function ($query) use ($authUser) {
                $query->where(function ($subQuery) use ($authUser) {
                    $subQuery->where('id', $authUser->department->id);
                    if (auth()->user()->department->name === 'QA') {
                        $subQuery->orWhere('name', 'QC');
                    }
                });
                // $query->where('id', $authUser->department->id)
                //         ->orWhere('creator_id', $authUser->id);
            });

            if ($isHead && $authUser->department->name === 'LOGISTIC') {
                $reportsQuery->orWhere(function ($query) {
                    $query->whereHas('department', function ($query) {
                        $query->where('name', 'STORE');
                    });
                });
            }
        }

        $reports = $reportsQuery->orderBy('created_at', 'desc')->paginate(10);

        return view('monthly_budget_report.index', compact('reports'));
    }

    public function create()
    {
        $departments = Department::all();

        return view('monthly_budget_report.create', compact('departments'));
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'dept_no' => 'required|integer',
            'creator_id' => 'required|integer',
            'report_date' => 'required|date',
            'created_autograph' => 'nullable|string',
            'is_known_autograph' => 'nullable|string',
            'approved_autograph' => 'nullable|string',
        ]);

        // Check if input method is set to 'excel' (indicating Excel file upload)
        if ($request->has('input_method') && $request->input_method == 'excel') {
            // Validate the Excel file upload
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls',
            ]);

            // Process the Excel file
            $excelFile = $request->file('excel_file');

            try {
                // Start a transaction
                DB::beginTransaction();

                // Create the main report entry
                $report = Report::create([
                    'dept_no' => $request->dept_no,
                    'creator_id' => $request->creator_id,
                    'report_date' => $request->report_date,
                    'created_autograph' => $request->created_autograph,
                    'is_known_autograph' => $request->is_known_autograph,
                    'approved_autograph' => $request->approved_autograph,
                ]);

                // Instantiate the import class with header_id
                $import = new MonthlyBudgetReportImport(
                    $request->dept_no,
                    $request->report_date,
                    $report->id,
                );

                // Import data from Excel file
                Excel::import($import, $excelFile);

                // Commit the transaction
                DB::commit();

                return redirect()
                    ->back()
                    ->with(
                        'success',
                        'Monthly Budget Report created successfully from Excel file.',
                    );
            } catch (\Exception $e) {
                // Rollback the transaction on error
                DB::rollBack();

                // Log the error (check laravel.log for details)
                Log::error('Error importing Excel file: '.$e->getMessage());

                return redirect()->back()->with('error', 'Error importing Excel file!');
            }
        } else {
            // Handle manual input data
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

            // Create the main report entry
            $report = Report::create([
                'dept_no' => $request->dept_no,
                'creator_id' => $request->creator_id,
                'report_date' => $request->report_date,
                'created_autograph' => $request->created_autograph,
                'is_known_autograph' => $request->is_known_autograph,
                'approved_autograph' => $request->approved_autograph,
            ]);

            // Iterate over items and create details
            foreach ($request->items as $item) {
                Detail::create([
                    'header_id' => $report->id,
                    'name' => $item['name'],
                    'spec' => $item['spec'] ?? null,
                    'uom' => $item['uom'],
                    'last_recorded_stock' => $item['last_recorded_stock'] ?? null,
                    'usage_per_month' => $item['usage_per_month'] ?? null,
                    'quantity' => $item['quantity'],
                    'remark' => $item['remark'],
                ]);
            }

            return redirect()
                ->back()
                ->with('success', 'Monthly Budget Report created successfully from manual input.');
        }
    }

    public function edit($id)
    {
        $departments = Department::all();
        $report = MonthlyBudgetReport::find($id);
        $details = $report->details;

        return view('monthly_budget_report.edit', compact('departments', 'report', 'details'));
    }

    public function show($id)
    {
        $report = Report::with('details', 'department')->find($id);

        // Extract the month name
        $reportDate = Carbon::parse($report->report_date);
        $monthName = $reportDate->format('F'); // Full month name
        $year = $reportDate->format('Y'); // Year
        $monthYear = $monthName.' '.$year;

        $this->updateStatus($report);

        return view('monthly_budget_report.detail', compact('report'));
    }

    public function update(UpdateMonthlyBudgetReportRequest $request, $id)
    {
        $validated = $request->validated();
        $report = Report::find($id);
        // Update the report with the remaining data
        $report->update($validated);

        return redirect()->back()->with('success', 'Monthly Budget Report successfully updated!');
    }

    public function destroy($id)
    {
        Report::find($id)->delete();
        Detail::where('header_id', $id)->delete();

        return redirect()->back()->with('success', 'Monthly Budget Report successfully deleted!');
    }

    public function saveAutograph(Request $request, $id)
    {
        $report = Report::with('department', 'user')->find($id);
        $report->update($request->all());
        $this->updateStatus($report);

        return redirect()->back()->with('success', 'Monthly Budget Report successfully approved!');
    }

    public function downloadExcelTemplate(Request $request)
    {
        $deptNo = $request->dept_no;

        // Generate Excel template using MonthlyBudgetReportTemplateExport
        return Excel::download(
            new MonthlyBudgetReportTemplateExport($deptNo),
            'monthly_budget_template.xlsx',
        );
    }

    public function reject(Request $request, $id)
    {
        Report::find($id)->update([
            'reject_reason' => $request->description,
            'is_reject' => 1,
        ]);

        return redirect()->back()->with('success', 'Monthly Budget Report successfully rejected!');
    }

    public function cancel(Request $request, $id)
    {
        Report::find($id)->update([
            'is_cancel' => true,
            'cancel_reason' => $request->description,
            'status' => 5,
        ]);

        return redirect()->back()->with('success', 'Monthly Budget Report successfully cancelled!');
    }

    private function updateStatus($report)
    {
        if ($report->is_reject === 1) {
            $report->status = 7;
        } elseif ($report->approved_autograph) {
            $report->status = 6;
        } elseif ($report->is_known_autograph) {
            if ($report->department->name === 'MOULDING') {
                $report->status = 3;
            } elseif ($report->department->name === 'QA' || $report->department->name === 'QC') {
                $report->status = 5;
            } else {
                $report->status = 4;
            }
        } elseif ($report->created_autograph) {
            $report->status = 2;
            if ($report->department->name === 'PLASTIC INJECTION') {
                $report->status = 4;
            }
        }

        $report->save();
    }
}
