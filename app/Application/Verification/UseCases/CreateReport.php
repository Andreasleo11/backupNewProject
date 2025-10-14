<?php

namespace App\Application\Verification\UseCases;

use App\Application\Verification\DTOs\ItemData;
use App\Application\Verification\DTOs\ReportData;
use App\Domain\Verification\Repositories\VerificationReportRepository;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Illuminate\Support\Facades\DB;

final class CreateReport
{
    public function __construct(
        private VerificationReportRepository $repo
    ) {}

    /**
     * @param  ItemData[]  $items
     */
    public function handle(ReportData $data, array $items, int $creatorId): VerificationReport
    {
        return DB::transaction(function () use ($data, $items, $creatorId) {
            $report = new VerificationReport($data->toArray());
            $report->creator_id = $creatorId;
            $report->document_number = $this->repo->nextDocumentNumber();
            $report->status = 'DRAFT';
            $this->repo->store($report);

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
