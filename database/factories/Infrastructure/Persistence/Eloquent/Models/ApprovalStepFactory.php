<?php

declare(strict_types=1);

namespace Database\Factories\Infrastructure\Persistence\Eloquent\Models;

use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApprovalStepFactory extends Factory
{
    protected $model = ApprovalStep::class;

    public function definition(): array
    {
        return [
            'approval_request_id' => ApprovalRequest::factory(),
            'sequence' => 1,
            'approver_type' => 'role',  // 'role' or 'user'
            'approver_id' => null,  // Role/User ID
            'user_signature_id' => null,
            'acted_by' => null,  // User ID who acted
            'status' => 'PENDING',  // Enum: PENDING, APPROVED, REJECTED
            'acted_at' => null,
            'remarks' => null,
            'signature_image_path' => null,
            'signature_sha256' => null,
        ];
    }

    public function withRole(int $roleId): self
    {
        return $this->state(fn (array $attributes) => [
            'approver_type' => 'role',
            'approver_id' => $roleId,
        ]);
    }

    public function withUser(int $userId): self
    {
        return $this->state(fn (array $attributes) => [
            'approver_type' => 'user',
            'approver_id' => $userId,
        ]);
    }

    public function approved(int $userId = 1): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'APPROVED',
            'acted_at' => now(),
            'acted_by' => $userId,
        ]);
    }

    public function rejected(int $userId = 1, string $reason = 'Rejected for testing'): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'REJECTED',
            'acted_at' => now(),
            'acted_by' => $userId,
            'remarks' => $reason,
        ]);
    }
}
