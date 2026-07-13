<?php

declare(strict_types=1);

namespace App\Domain\Verification\Services;

use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use App\Mail\QaqcMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

final class VerificationEmailService
{
    /**
     * Send report email with PDF and file attachments to custom recipients.
     */
    public function sendEmail(int $reportId, array $emailData, VerificationExportService $exportService): void
    {
        $pdfPath = $exportService->savePdf($reportId);
        $report  = VerificationReport::with('files')->findOrFail($reportId);

        $filePaths   = $report->files->map(fn ($f) => Storage::url('files/' . basename($f->name)))->toArray();
        $filePaths[] = Storage::url($pdfPath);

        Mail::send(new QaqcMail([
            'to'         => $this->parseEmails($emailData['to'] ?? ''),
            'cc'         => $this->parseEmails($emailData['cc'] ?? ''),
            'subject'    => $emailData['subject'] ?? 'Verification Report Mail',
            'body'       => $emailData['body'] ?? 'Mail from ' . config('app.name'),
            'file_paths' => $filePaths,
        ]));
    }

    /**
     * Parse semicolon-separated email string into array.
     */
    private function parseEmails(string $emailString): array
    {
        return array_filter(array_map('trim', explode(';', $emailString)));
    }
}
