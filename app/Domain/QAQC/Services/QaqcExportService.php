<?php

declare(strict_types=1);

namespace App\Domain\QAQC\Services;

use App\Exports\FormAdjustExport;
use App\Exports\MonthlyReportsExport;
use App\Exports\ReportsExport;
use App\Models\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class QaqcExportService
{
    /**
     * Export all reports to Excel.
     */
    public function exportReportsToExcel(): BinaryFileResponse
    {
        return Excel::download(new ReportsExport, 'reports-all-data.xlsx');
    }

    /**
     * Export form adjust to Excel.
     */
    public function exportFormAdjustToExcel(): BinaryFileResponse
    {
        return Excel::download(new FormAdjustExport, 'formadjust-all-data.xlsx');
    }

    /**
     * Export monthly report to Excel.
     */
    public function exportMonthlyReport(string $monthData): BinaryFileResponse
    {
        $month = Carbon::parse($monthData)->month;
        $year = Carbon::parse($monthData)->year;
        $monthName = Carbon::parse($monthData)->format('F');

        $filename = "VQC MonthlyReport {$monthName}-{$year}.xlsx";

        return Excel::download(new MonthlyReportsExport($month, $year), $filename);
    }

    /**
     * Export report to PDF.
     */
    public function exportToPdf(int $reportId)
    {
        $report = Report::with('details')->findOrFail($reportId);
        $preparedData = $this->prepareReportData($report);

        $pdf = Pdf::loadView(
            'pdf/verification-report-pdf',
            $preparedData
        )->setPaper('a4', 'landscape');

        return $pdf->download('verification-report-' . $report->id . '.pdf');
    }

    /**
     * Preview PDF in browser.
     */
    public function previewPdf(int $reportId)
    {
        $report = Report::with('details')->findOrFail($reportId);
        $preparedData = $this->prepareReportData($report);

        return view('pdf/verification-report-pdf', $preparedData);
    }

    /**
     * Save PDF to storage.
     */
    public function savePdf(int $reportId): string
    {
        $report = Report::with('details')->findOrFail($reportId);
        $preparedData = $this->prepareReportData($report);

        $pdf = Pdf::loadView(
            'pdf/verification-report-pdf',
            $preparedData
        )->setPaper('a4', 'landscape');

        $fileName = 'verification-report-' . $report->id . '.pdf';
        $filePath = 'pdfs/' . $fileName;

        Storage::disk('public')->put($filePath, $pdf->output());

        return $filePath;
    }

    /**
     * Prepare report data for PDF generation.
     */
    private function prepareReportData(Report $report): array
    {
        $user = Auth::user();

        foreach ($report->details as $pd) {
            $pd->daijo_defect_detail = json_decode($pd->daijo_defect_detail);
            $pd->customer_defect_detail = json_decode($pd->customer_defect_detail);
            $pd->supplier_defect_detail = json_decode($pd->supplier_defect_detail);
            $pd->remark = json_decode($pd->remark);
        }

        $autographNames = [
            'autograph_name_1' => $report->autograph_user_1 ?? null,
            'autograph_name_2' => $report->autograph_user_2 ?? null,
            'autograph_name_3' => $report->autograph_user_3 ?? null,
        ];

        return compact('report', 'user', 'autographNames');
    }
}
