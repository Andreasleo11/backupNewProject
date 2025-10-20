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
                $itemModel = $report->items()->create([
                    'part_name' => $item->part_name,
                    'rec_quantity' => $item->rec_quantity,
                    'verify_quantity' => $item->verify_quantity,
                    'can_use' => $item->can_use,
                    'cant_use' => $item->cant_use,
                    'price' => $item->price,
                    'currency' => $item->currency,
                ]);

                foreach ($item->defects as $defect) {
                    $itemModel->defects()->create([
                        'code' => $defect->code,
                        'name' => $defect->name,
                        'severity' => $defect->severity,
                        'source' => $defect->source,
                        'quantity' => $defect->quantity,
                        'notes' => $defect->notes,
                    ]);
                }
            }

            return $report->fresh(['items']);
        });
    }
}
