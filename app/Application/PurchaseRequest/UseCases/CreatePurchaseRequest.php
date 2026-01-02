<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\DTOs\CreatePurchaseRequestDTO;
use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use App\Models\Department;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;

final class CreatePurchaseRequest
{
    public function __construct(
        private PurchaseRequestRepository $repo,
        private Approvals $approvals,
    ) {}

    public function handle(CreatePurchaseRequestDTO $dto): PurchaseRequest
    {
        return DB::transaction(function () use ($dto) {
            $header = $this->buildHeader($dto);

            $pr = $this->repo->create($header);

            $items = $this->buildItems($dto, $pr->from_department);
            $this->repo->addItems($pr, $items);

            // Submit to approval engine if final
            if (! $dto->isDraft && ! $pr->approvalRequest) {
                $pr = $this->repo->loadForApprovalContext($pr);
                $ctx = $pr->buildApprovalContext(); // keep your existing method for now
                $this->approvals->submit($pr, $dto->requestedByUserId, $ctx);
            }

            return $pr;
        });
    }

    private function buildHeader(CreatePurchaseRequestDTO $dto): array
    {
        // Determine office/factory using your existing rules
        $officeDepartments = Department::where('is_office', true)
            ->pluck('name')
            ->map(fn ($n) => strtoupper($n))
            ->toArray();

        $from = strtoupper($dto->fromDepartment);

        $type = in_array($from, $officeDepartments, true) ? 'office' : 'factory';
        if ($from === 'PE') $type = 'factory';

        $status = $dto->isDraft ? 8 : 1;

        // your special cases
        if (! $dto->isDraft) {
            if ($from === 'PLASTIC INJECTION' || ($from === 'MAINTENANCE MACHINE' && $dto->branch === 'KARAWANG')) {
                $status = 7;
            }
            if ($from === 'PERSONALIA') {
                $status = 6;
            }
        }

        $header = [
            'user_id_create' => $dto->requestedByUserId,
            'from_department' => $from,
            'to_department' => strtoupper($dto->toDepartment),
            'date_pr' => $dto->datePr,
            'date_required' => $dto->dateRequired,
            'remark' => $dto->remark,
            'supplier' => $dto->supplier,
            'pic' => $dto->pic,
            'type' => $type,
            'branch' => $dto->branch,
            'status' => $status,
        ];

        return $header;
    }

    private function buildItems(CreatePurchaseRequestDTO $dto, string $fromDepartment): array
    {
        $autoHeadApprove = in_array($fromDepartment, ['PERSONALIA','PLASTIC INJECTION','MAINTENANCE MACHINE'], true);

        return array_map(function ($item) use ($autoHeadApprove) {
            return [
                'item_name' => $item->itemName,
                'quantity' => $item->quantity,
                'purpose' => $item->purpose,
                'price' => $item->price,
                'uom' => strtoupper($item->uom),
                'currency' => $item->currency,
                'is_approve_by_head' => $autoHeadApprove ? 1 : null,
            ];
        }, $dto->items);
    }
}
