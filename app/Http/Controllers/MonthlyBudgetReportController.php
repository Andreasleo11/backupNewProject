<?php

namespace App\Http\Controllers;

use App\Exports\MonthlyBudgetReportTemplateExport;
use App\Imports\MonthlyBudgetReportImport;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\MonthlyBudgetReport as Report;
use App\Models\MonthlyBudgetReportDetail as Detail;
use App\Models\User;
use App\Notifications\MonthlyBudgetReportRequestSign;
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
        $isDirector = $authUser->department->name === 'DIRECTOR';

        $reportsQuery = Report::with('department', 'details');

        if($authUser->email == 'nur@daijo.co.id'){
            $reportsQuery = Report::whereNotNull('created_autograph')->whereNotNull('is_known_autograph')->whereNotNull('approved_autograph');
        } elseif($isDirector){
            $reportsQuery = Report::whereNotNull('created_autograph')->whereNotNull('is_known_autograph')->whereHas('department', function($query){
                $query->where('name', 'QA')->orWhere('name', 'QC');
            });
        } elseif($isGm){
            $reportsQuery = Report::whereNotNull('created_autograph')->whereNotNull('is_known_autograph')->whereHas('department', function($query){
                $query->whereNot(function($query){
                    $query->where('name', 'QA')->orWhere('name', 'QC')->orWhere('name', 'MOULDING');
                });
            });
        } elseif($isHead){
            $reportsQuery = Report::whereNotNull('created_autograph');
        }

        // filter by auth user department or if it's user create the report besides DIRECTOR and GM
        if(!($isDirector || $isGm)){
            $reportsQuery->whereHas('department', function($query) use($authUser) {
                $query->where('id', $authUser->department->id)->orWhere('creator_id', $authUser->id);
            });
        }

        $reports = $reportsQuery->get();
        // dd($reports);
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
            'creator_id' => 'nullable|integer',
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
                $import = new MonthlyBudgetReportImport($request->dept_no, $request->report_date, $report->id);

                // Import data from Excel file
                Excel::import($import, $excelFile);

                // Commit the transaction
                DB::commit();

                $this->sendNotification($report);
                return redirect()->back()->with('success', 'Monthly Budget Report created successfully from Excel file.');
            } catch (\Exception $e) {
                // Rollback the transaction on error
                DB::rollBack();

                 // Log the error (check laravel.log for details)
                Log::error('Error importing Excel file: ' . $e->getMessage());

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
            $this->sendNotification($report);
            return redirect()->back()->with('success', 'Monthly Budget Report created successfully from manual input.');
        }
    }

    public function show($id)
    {
        $report = Report::with('details', 'department')->find($id);
        return view('monthly_budget_report.detail', compact('report'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'dept_no' => 'integer',
            'report_date' => 'date',
        ]);

        Report::find($id)->update($request);
        return redirect()->back()->with('status', 'Monthly Budget Report successfully updated!');
    }

    public function destroy($id)
    {
        Report::find($id)->delete();
        Detail::where('header_id', $id)->delete();
        return redirect()->back()->with('status', 'Monthly Budget Report successfully deleted!');
    }

    public function saveAutograph(Request $request, $id)
    {
        $report = Report::with('department', 'user')->find($id);
        $report->update($request->all());
        $this->sendNotification($report);
        return redirect()->back()->with('status', 'Monthly Budget Report successfully approved!');
    }

    public function downloadExcelTemplate(Request $request)
    {
        $deptNo = $request->dept_no;
        // Generate Excel template using MonthlyBudgetReportTemplateExport
        return Excel::download(new MonthlyBudgetReportTemplateExport($deptNo), 'monthly_budget_template.xlsx');
    }

    private function sendNotification($report)
    {
        $detail = [
            'greeting' => 'Monthly Budget Report Notification',
            'body' => 'We waiting for your sign!',
            'actionText' => 'Click to see the detail',
            'actionURL' => env('APP_URL', 'http://116.254.114.93:2420/') . 'monthlyBudgetReport/' . $report->id,
        ];

        // $creator = User::find($report->creator_id)->notify(new MonthlyBudgetReportRequestSign($report, $detail));

        if($report->created_autograph && !$report->is_known_autograph && !$report->approved_autograph){
            if($report->department->name === 'MOULDING'){
                $user = User::with('department', 'specification')->whereHas('department', function($query){
                    $query->where('name', 'MOULDING');
                })->where('is_head', 1)->whereHas('specification', function($query){
                    $query->where('name', 'design');
                })->first();
            } else {
                $user = User::where('department_id', $report->department->id)->where('is_head', 1)->first();
            }
        } elseif($report->created_autograph && $report->is_known_autograph && !$report->approved_autograph){
            if($report->department->name === 'MOULDING'){
                $user = User::with('department', 'specification')->whereHas('department', function($query){
                    $query->where('name', 'MOULDING');
                })->where('is_head', 1)->whereHas('specification', function($query){
                    $query->where('name', '!=', 'design');
                })->first();
            } elseif($report->department->name === "QA" || $report->department->name === "QC"){
                $user = User::with('department')->whereHas('department', function($query){
                    $query->where('name', 'DIRECTOR');
                })->first();
            } else {
                $user = User::where('is_gm', 1)->first();
            }
        } elseif($report->created_autograph && $report->is_known_autograph && $report->approved_autograph){
            $user = User::where('email', 'nur@daijo.co.id')->first();
            $detail['body'] = "Monthly Budget Report signed!";

            // notify the creator if already signed all
            $report->user->notify(new MonthlyBudgetReportRequestSign($report, $detail));
        }

        if($user){
            try {
                $detail['userName'] = $user->name;
                $user->notify(new MonthlyBudgetReportRequestSign($report, $detail));

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
