<?php

namespace App\Console\Commands;

use App\Mail\VQCNotificationMail;
use App\Models\Report;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SendReportNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-report-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send report status notification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $approvedDoc = Report::approved()->count();
        $waitingSignatureDoc = Report::waitingSignature()->count();
        $waitingApprovalDoc = Report::waitingApproval()->count();
        $rejectedDoc = Report::rejected()->count();

        $mailData = [
            'to' => Config::get('email.feature_qc.to'),
            'cc' => Config::get('email.feature_qc.cc'),
            'subject' => 'VQC Report Notification',
            'from' => 'pt.daijoindustrial@daijo.co.id',
            'approved' => $approvedDoc,
            'waitingSignature' => $waitingSignatureDoc,
            'waitingApproval' => $waitingApprovalDoc,
            'rejected' => $rejectedDoc,
            'url' => 'http://116.254.114.93:2420',
        ];

        Mail::send(new VQCNotificationMail($mailData));
        $this->info('Report status notification sent successfully.');
    }
}
