<?php

declare(strict_types=1);

namespace App\Domain\Verification\Services;

use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

final class VerificationExportService
{
    /**
     * Download PDF for a verification report.
     */
    public function exportToPdf(int $reportId)
    {
        $report = VerificationReport::with(['items.defects', 'approvalRequest.steps'])->findOrFail($reportId);

        return Pdf::loadView('pdf.verification-report', compact('report'))
            ->setPaper('a4', 'landscape')
            ->download("verification-report-{$report->document_number}.pdf");
    }

    /**
     * Preview PDF in browser.
     */
    public function previewPdf(int $reportId)
    {
        $report = VerificationReport::with(['items.defects', 'approvalRequest.steps'])->findOrFail($reportId);

        return view('pdf.verification-report', compact('report'));
    }

    /**
     * Save PDF to storage and return its path.
     */
    public function savePdf(int $reportId): string
    {
        $report = VerificationReport::with(['items.defects', 'approvalRequest.steps'])->findOrFail($reportId);

        $pdf = Pdf::loadView('pdf.verification-report', compact('report'))
            ->setPaper('a4', 'landscape');

        $filePath = "pdfs/verification-report-{$report->id}.pdf";
        Storage::disk('public')->put($filePath, $pdf->output());

        return $filePath;
    }
}
