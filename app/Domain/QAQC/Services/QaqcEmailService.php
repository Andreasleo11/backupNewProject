<?php

declare(strict_types=1);

namespace App\Domain\QAQC\Services;

use App\Mail\QaqcMail;
use App\Models\File;
use App\Models\Report;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

final class QaqcEmailService
{
    /**
     * Send email with report PDF and attachments.
     */
    public function sendEmail(int $reportId, array $emailData, QaqcExportService $exportService): void
    {
        $pdfPath = $exportService->savePdf($reportId);

        $report = Report::with('details')->findOrFail($reportId);
        $pdfUrl = Storage::url($pdfPath);

        $files = File::where('doc_id', $report->doc_num)->get();
        $filePaths = $files->map(fn ($file) => Storage::url('files/' . $file->name))->toArray();
        $filePaths[] = $pdfUrl;

        $mailData = [
            'to' => $this->parseEmails($emailData['to'] ?? ''),
            'cc' => $this->parseEmails($emailData['cc'] ?? ''),
            'subject' => $emailData['subject'] ?? 'QAQC Verification Report Mail',
            'body' => $emailData['body'] ?? 'Mail from ' . env('APP_NAME'),
            'file_paths' => $filePaths,
        ];

        Mail::send(new QaqcMail($mailData));
    }

    /**
     * Send email after redirect (from session data).
     */
    public function sendEmailFromSession(int $reportId, string $customer, QaqcExportService $exportService): void
    {
        $pdfPath = $exportService->savePdf($reportId);
        $pdfUrl = Storage::url($pdfPath);

        $to = Config::get('email.feature_qc.to');
        $cc = Config::get('email.feature_qc.cc');

        $mailData = [
            'to' => $to,
            'cc' => $cc,
            'subject' => 'QAQC Verification Report Mail ' . $customer,
            'body' => 'Mail from ' . env('APP_NAME'),
            'file_paths' => [$pdfUrl],
        ];

        Mail::send(new QaqcMail($mailData));
    }

    /**
     * Parse email addresses from semicolon-separated string.
     */
    private function parseEmails(string $emailString): array
    {
        return array_filter(array_map('trim', explode(';', $emailString)));
    }
}
