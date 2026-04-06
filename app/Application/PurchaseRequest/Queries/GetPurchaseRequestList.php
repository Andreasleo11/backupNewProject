<?php

namespace App\Application\PurchaseRequest\Queries;

use App\Application\PurchaseRequest\DTOs\GetPurchaseRequestListDTO;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\PurchaseRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class GetPurchaseRequestList
{
    public function __construct(
        private readonly \App\Application\PurchaseRequest\Queries\PurchaseRequestQueryBuilder $queryBuilder,
    ) {}

    public function handle(GetPurchaseRequestListDTO $dto): LengthAwarePaginator
    {
        $user = User::findOrFail($dto->userId);

        // Build scoped query using the consolidated QueryBuilder
        $query = $this->queryBuilder->forUser($user);

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
