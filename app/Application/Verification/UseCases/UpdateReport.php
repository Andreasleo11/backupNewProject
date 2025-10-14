<?php

namespace App\Application\Verification\UseCases;

use App\Application\Verification\DTOs\ItemData;
use App\Application\Verification\DTOs\ReportData;
use App\Domain\Verification\Repositories\VerificationReportRepository;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Illuminate\Support\Facades\DB;

final class UpdateReport
{
    public function __construct(
        private VerificationReportRepository $repo
    ) {}

    /**
     * @param  ItemData[]  $items
     */
    public function handle(int $reportId, ReportData $data, array $items, int $actorId): VerificationReport
    {
        /** @var VerificationReport $report */
        $report = $this->repo->findById($reportId);
        if (! $report) {
            throw new \RuntimeException('Report not found.');
        }
        if ($report->status !== 'DRAFT') {
            throw new \DomainException('Only DRAFT reports can be updated.');
        }
        if ($report->creator_id !== $actorId) {
            throw new \DomainException('Only the creator can update this report.');
        }

        return DB::transaction(function () use ($report, $data, $items) {
            $report->update($data->toArray());

            // simple replace strategy; switch to diff/patch if needed
            $report->items()->delete();
            foreach ($items as $item) {
                $report->items()->create([
                    'name' => $item->name,
                    'notes' => $item->notes,
                    'amount' => $item->amount,
                ]);
            }

            return $report->fresh(['items']);
        });
    }
}
