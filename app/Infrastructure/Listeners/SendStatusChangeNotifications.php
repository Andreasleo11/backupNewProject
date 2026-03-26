<?php

namespace App\Infrastructure\Listeners;

use App\Infrastructure\Events\ReportStatusChanged;
use App\Infrastructure\Notifications\ReportApproved;
use App\Infrastructure\Notifications\ReportRejected;
use App\Infrastructure\Notifications\ReportSubmitted;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;

class SendStatusChangeNotifications
{
    public function handle(ReportStatusChanged $e): void
    {
        $report = VerificationReport::with('items')->find($e->reportId);
        if (! $report) {
            return;
        }

        // decide recipients: creator, current approver(s), directors, etc.
        $creator = $report->creator_id ? \App\Models\User::find($report->creator_id) : null;

        match ($e->to) {
            'IN_REVIEW' => $creator?->notify(new ReportSubmitted($report)),
            'APPROVED' => $creator?->notify(new ReportApproved($report)),
            'REJECTED' => $creator?->notify(new ReportRejected($report)),
            default => null
        };
    }
}
