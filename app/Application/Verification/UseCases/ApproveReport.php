<?php

namespace App\Application\Verification\UseCases;

use App\Domain\Approval\Contracts\Approvals;
use App\Domain\Verification\Repositories\VerificationReportRepository;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;

final class ApproveReport
{
    public function __construct(
        private VerificationReportRepository $repo,
        private Approvals $approvals
    ) {}

    public function handle(int $reportId, int $actorId, ?string $remarks = null): void
    {
        /** @var VerificationReport $report */
        $report = $this->repo->findById($reportId);
        if (! $report) {
            throw new \RuntimeException('Report not found.');
        }
        if ($report->status !== 'IN_REVIEW') {
            throw new \DomainException('Report is not in review.');
        }

        // engine validates step actor; will throw if not allowed
        $this->approvals->approve($report, $actorId, $remarks);

        // if engine completed final step, mirror status for fast listing
        $report->refresh();
        $req = $report->approvalRequest;
        if ($req && $req->status === 'APPROVED') {
            $report->update(['status' => 'APPROVED']);
        }
    }
}
