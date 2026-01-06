<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\DTOs\CreatePurchaseRequestDTO;
use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use App\Domain\PurchaseRequest\Services\PurchaseRequestApprovalContextBuilder;
use App\Domain\PurchaseRequest\Services\PurchaseRequestNumberGenerator;
use App\Domain\PurchaseRequest\Services\PurchaseRequestStatusCalculator;
use App\Domain\PurchaseRequest\Services\PurchaseRequestTypeResolver;
use App\Events\PurchaseRequestCreated;
use App\Models\PurchaseRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class CreatePurchaseRequest
{
    public function __construct(
        private PurchaseRequestRepository $repo,
        private Approvals $approvals,
        private PurchaseRequestNumberGenerator $numberGenerator,
        private PurchaseRequestStatusCalculator $statusCalculator,
        private PurchaseRequestTypeResolver $typeResolver,
        private PurchaseRequestApprovalContextBuilder $contextBuilder,
    ) {}

    public function handle(CreatePurchaseRequestDTO $dto): PurchaseRequest
    {
        return DB::transaction(function () use ($dto) {
            $header = $this->buildHeader($dto);

            // Generate Doc Num before create
            $header['doc_num'] = $this->numberGenerator->generateDocNum(
                $dto->toDepartment,
                $dto->branch,
                Carbon::parse($dto->datePr) // Or use now() if datePr is just input date
            );
            // PR No depends on ID, so we fill it temporarily or update after
            $header['pr_no'] = 'TMP';

            $pr = $this->repo->create($header);

            // Update PR No
            $pr->pr_no = $this->numberGenerator->generatePrNo($dto->toDepartment, $pr->id);
            $pr->saveQuietly(); // Use saveQuietly to avoid triggering events if we were still using Model events

            $items = $this->buildItems($dto, $pr->from_department);
            $this->repo->addItems($pr, $items);

            // Submit to approval engine if final
            if (! $dto->isDraft && ! $pr->approvalRequest) {
                $pr = $this->repo->loadForApprovalContext($pr);

                // Build approval context using Domain Service
                $ctx = $this->contextBuilder->build(
                    fromDepartment: $pr->from_department,
                    toDepartment: $pr->to_department->value,
                    branch: $pr->branch,
                    isOffice: $pr->type === 'office',
                    items: $pr->items->toArray()
                );

                $this->approvals->submit($pr, $dto->requestedByUserId, $ctx);
            }

            // Dispatch Event
            PurchaseRequestCreated::dispatch($pr);

            return $pr;
        });
    }

    private function buildHeader(CreatePurchaseRequestDTO $dto): array
    {
        $from = strtoupper($dto->fromDepartment);

        // Use Domain Services to calculate type and status
        $type = $this->typeResolver->resolve($dto->fromDepartment);
        $status = $this->statusCalculator->calculateInitialStatus(
            fromDepartment: $from,
            branch: $dto->branch,
            isDraft: $dto->isDraft
        );

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
        $autoHeadApprove = in_array($fromDepartment, ['PERSONALIA', 'PLASTIC INJECTION', 'MAINTENANCE MACHINE'], true);

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
