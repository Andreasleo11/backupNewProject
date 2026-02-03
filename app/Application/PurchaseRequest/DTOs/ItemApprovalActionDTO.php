<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\DTOs;

use App\Infrastructure\Persistence\Eloquent\Models\User;

/**
 * DTO for item approval/rejection actions.
 */
final readonly class ItemApprovalActionDTO
{
    public function __construct(
        public int $itemId,
        public User $actorUser,
    ) {}
}
