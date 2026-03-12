<?php

namespace App\Http\Controllers;

use App\Domain\DailyReport\Services\DailyReportAnalyticsService;
use App\Domain\DailyReport\Services\DailyReportUploadService;
use App\Models\EmployeeDailyReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EmployeeDailyReportController extends Controller
{
    public function __construct(
        private readonly DailyReportUploadService $uploadService,
        private readonly DailyReportAnalyticsService $analyticsService
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = EmployeeDailyReport::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('employee_name', 'like', '%' . $search . '%')
                    ->orWhere('work_description', 'like', '%' . $search . '%');
            });
        }

        if ($from) {
            $query->whereDate('work_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('work_date', '<=', $to);
        }

        $employeeNik = Session::get('logged_in_employee_nik');
        $query->where('employee_id', $employeeNik);
        $query->orderBy('work_date', 'desc');

        $reports = $query->paginate(20)->withQueryString();

        return view('employee.index', compact('reports'));
    }

    public function showUploadForm()
    {
        return view('dailyreport.upload-daily-report');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'report_file' => 'required|file|mimes:xlsx,csv,txt',
        ]);

        $result = $this->uploadService->processExcelUpload($request->file('report_file'));

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return view('dailyreport.preview', ['previewData' => $result['data']]);
    }

    public function confirmUpload(Request $request)
    {
        $encoded = $request->input('data');
        $rows = unserialize(base64_decode($encoded));

        $logs = $this->uploadService->confirmUpload($rows);

        return view('dailyreport.upload-log', compact('logs'));
    }

    public function show(Request $request, $employee_id)
    {
        $user = auth()->user();

        $filters = [
            'filter_start_date' => $request->input('filter_start_date'),
            'filter_end_date' => $request->input('filter_end_date'),
        ];

        $data = $this->analyticsService->getEmployeeReports(
            $employee_id,
            $user->department?->dept_no,
            $filters,
            $user
        );

        return view('dailyreport.depthead_show', [
            'reports' => $data['reports'],
            'employee_id' => $employee_id,
            'filter_start_date' => $filters['filter_start_date'],
            'filter_end_date' => $filters['filter_end_date'],
            'missingDates' => $data['missing_dates'],
            'submittedDates' => $data['submitted_dates'],
            'startDate' => $data['start_date'],
            'endDate' => $data['end_date'],
            'calendarEvents' => $data['calendar_events'],
        ]);
    }
}
