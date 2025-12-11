<?php

namespace App\Application\Verification\UseCases;

use App\Domain\Verification\Repositories\VerificationReportRepository;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Illuminate\Support\Facades\DB;

final class DeleteReport
{
    public function __construct(
        private VerificationReportRepository $repo
    ) {}

    public function handle(int $reportId, int $actorId): void
    {
        /** @var VerificationReport $report */
        $report = $this->repo->findById($reportId);
        if (! $report) {
            return; // idempotent
        }

        if (! in_array($report->status, ['DRAFT', 'REJECTED'], true)) {
            throw new \DomainException('Only DRAFT or REJECTED reports can be deleted.');
        }
        if ($report->creator_id !== $actorId) {
            throw new \DomainException('Only the creator can delete this report.');
        }

        DB::transaction(function () use ($report) {
            $report->items()->delete();
            $report->delete();
        });
    }
}
