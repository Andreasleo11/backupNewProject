<?php

namespace App\Application\Verification\UseCases;

use App\Domain\Approval\Contracts\Approvals;
use App\Domain\Verification\Repositories\VerificationReportRepository;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;

final class SubmitReport
{
    public function __construct(
        private VerificationReportRepository $repo,
        private Approvals $approvals
    ) {}

    public function handle(int $reportId, int $actorId): void
    {
        /** @var VerificationReport $report */
        $report = $this->repo->findById($reportId);
        if (! $report) {
            throw new \RuntimeException('Report not found.');
        }
        if ($report->status !== 'DRAFT') {
            throw new \DomainException('Only DRAFT reports can be submitted.');
        }
        if ($report->creator_id !== $actorId) {
            throw new \DomainException('Only the creator can submit this report.');
        }

        $context = [
            'department' => data_get($report->meta, 'department'),
            'amount' => (float) $report->items()->sum('amount'),
            'tags' => ['verification'],
        ];

        // submit to generic approval engine
        $this->approvals->submit($report, $actorId, $context);

        // keep a lightweight mirror on the report for quick UI filters
        $report->update(['status' => 'IN_REVIEW']);
    }
}
