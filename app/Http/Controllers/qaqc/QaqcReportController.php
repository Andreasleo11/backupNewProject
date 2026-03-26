<?php

namespace App\Http\Controllers\qaqc;

use App\DataTables\VqcReportsDataTable;
use App\Domain\QAQC\Services\QaqcEmailService;
use App\Domain\QAQC\Services\QaqcExportService;
use App\Domain\QAQC\Services\QaqcReportService;
use App\Http\Controllers\Controller;
use App\Models\HeaderFormAdjust;
use App\Models\MasterDataPartNumberPrice;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QaqcReportController extends Controller
{
    public function __construct(
        private readonly QaqcReportService $reportService,
        private readonly QaqcExportService $exportService,
        private readonly QaqcEmailService $emailService
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request, VqcReportsDataTable $dataTable)
    {
        $status = $request->status;
        $reports = $this->reportService->getReports($status);

        return $dataTable->with('status', $status)->render('qaqc.reports.index', compact('reports', 'status'));
    }

    public function detail($id)
    {
        $report = Report::with('details', 'details.defects', 'details.defects.category', 'files')->findOrFail($id);
        $user = Auth::user();
        $autographNames = [
            'autograph_name_1' => $report->autograph_user_1 ?? null,
            'autograph_name_2' => $report->autograph_user_2 ?? null,
            'autograph_name_3' => $report->autograph_user_3 ?? null,
        ];
        $adjustForm = HeaderFormAdjust::where('report_id', $report->id)->first();

        return view('qaqc.reports.detail', compact('report', 'user', 'autographNames', 'adjustForm'));
    }

    public function getItemPrice(Request $request)
    {
        $itemName = urldecode($request->input('name'));
        $latestPrice = MasterDataPartNumberPrice::where('name', $itemName)->latest('created_at')->value('price');

        return response()->json(['latest_price' => $latestPrice]);
    }

    public function destroy($id)
    {
        $this->reportService->deleteReport($id);

        return redirect()->route('qaqc.report.index')->with('success', 'Report has been deleted successfully!');
    }

    public function uploadAttachment(Request $request, $id)
    {
        $request->validate(['attachment' => 'required|mimes:pdf,doc,docx,xlsx,xls,png,jpg,jpeg']);
        $this->reportService->uploadAttachment($id, $request->file('attachment'));

        return redirect()->back()->with('success', 'Attachment uploaded and saved successfully!');

    }

    public function saveImagePath(Request $request, $reportId, $section)
    {
        $username = Auth::check() ? Auth::user()->name : '';
        $this->reportService->saveAutograph($reportId, (int) $section, $username);

        return response()->json(['success' => 'Autograph saved successfully!']);
    }

    public function exportToPdf($id)
    {
        return $this->exportService->exportToPdf($id);
    }

    public function previewPdf($id)
    {
        return $this->exportService->previewPdf($id);
    }

    public function redirectToIndex()
    {
        $id = session()->get('header')->id ?? session()->get('header_edit')->id;

        if ($id) {
            $cust = session()->get('header')->customer ?? session()->get('header_edit')->customer;
            $this->emailService->sendEmailFromSession($id, $cust, $this->exportService);
        }

        return redirect()->route('qaqc.report.index')->with(['success' => 'Report succesfully stored/updated!']);
    }

    public function savePdf($id)
    {
        $filePath = $this->exportService->savePdf($id);

        return redirect()->back()->with(['message' => 'PDF saved successfully', 'file_path' => $filePath]);
    }

    public function sendEmail($id, Request $request)
    {
        $emailData = $request->only(['to', 'cc', 'subject', 'body']);
        $this->emailService->sendEmail($id, $emailData, $this->exportService);
        $this->reportService->markAsEmailed($id);

        return redirect()->back()->with(['success' => 'Email sent successfully!']);
    }

    public function rejectAuto(Request $request, $id)
    {
        $this->reportService->rejectReport($id, $request->description);

        return redirect()->back()->with(['success' => 'Report rejected automatically!']);
    }

    public function lock($id)
    {
        $this->reportService->lockReport($id);

        return redirect()->back()->with(['success' => 'Report locked successfully!']);
    }

    public function exportToExcel()
    {
        return $this->exportService->exportReportsToExcel();
    }

    public function exportFormAdjustToExcel()
    {
        return $this->exportService->exportFormAdjustToExcel();
    }

    public function updateDoNumber(Request $request, $id)
    {
        $this->reportService->updateDoNumber($id, $request->do_num);

        return redirect()->back()->with('success', 'Update do number berhasil!');
    }

    public function monthlyreport()
    {
        $result = $this->reportService->getMonthlyReportData();

        return view('qaqc.monthlyreport', compact('result'));
    }

    public function showDetails(Request $request)
    {
        $reports = $this->reportService->getMonthlyReportDetails($request->monthData);

        return view('qaqc.monthlyreportdetail', compact('reports'));
    }

    public function export(Request $request)
    {
        return $this->exportService->exportMonthlyReport($request->monthData);
    }
}
