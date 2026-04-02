<?php

namespace App\Application\PurchaseRequest\Queries;

use App\Application\PurchaseRequest\DTOs\GetPurchaseRequestListDTO;
use App\Application\PurchaseRequest\Services\PurchaseRequestQueryScoper;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\PurchaseRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class GetPurchaseRequestList
{
    public function __construct(
        private readonly PurchaseRequestQueryScoper $queryScoper,
    ) {}

    public function handle(GetPurchaseRequestListDTO $dto): LengthAwarePaginator
    {
        $user = User::findOrFail($dto->userId);

        $query = PurchaseRequest::query()
            ->with(['files', 'createdBy']);

        // Apply User Scoping
        $query = $this->queryScoper->scopeForUser($user, $query);

        // Apply Date Filter
        if ($dto->startDate && $dto->endDate) {
            $query->whereBetween('date_pr', [$dto->startDate, $dto->endDate]);
        }

        // Apply Status Filter
        if ($dto->status) {
            $query->where('status', $dto->status);
        }

        // Apply Branch Filter
        if ($dto->branch) {
            $query->where('branch', $dto->branch);
        }

        // Apply Sorting
        $query->orderBy('created_at', 'desc');

        return $query->paginate($dto->perPage);
    }
}
