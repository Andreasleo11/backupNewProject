<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use App\Mail\VQCNotificationMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SendReportNotification extends Command
{
    protected $signature = 'email:send-report-notification';

    protected $description = 'Send verification report status notification';

    public function handle(): void
    {
        // ponytail: replaces legacy Report scopes with VerificationReport status counts
        $counts = VerificationReport::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $mailData = [
            'to'               => Config::get('email.feature_qc.to'),
            'cc'               => Config::get('email.feature_qc.cc'),
            'subject'          => 'VQC Report Notification',
            'from'             => 'pt.daijoindustrial@daijo.co.id',
            'approved'         => (int) ($counts['APPROVED'] ?? 0),
            'waitingSignature' => (int) ($counts['DRAFT'] ?? 0),
            'waitingApproval'  => (int) ($counts['IN_REVIEW'] ?? 0),
            'rejected'         => (int) ($counts['REJECTED'] ?? 0),
            'url'              => config('app.url'),
        ];

        Mail::send(new VQCNotificationMail($mailData));
        $this->info('Report status notification sent successfully.');
    }
}
