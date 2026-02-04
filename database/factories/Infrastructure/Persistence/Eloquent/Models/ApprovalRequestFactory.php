<?php

declare(strict_types=1);

namespace Database\Factories\Infrastructure\Persistence\Eloquent\Models;

use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Models\PurchaseRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApprovalRequestFactory extends Factory
{
    protected $model = ApprovalRequest::class;

    public function definition(): array
    {
        return [
            'approvable_id' => PurchaseRequest::factory(),
            'approvable_type' => PurchaseRequest::class,
            'current_step' => 1,
            'status' => 'DRAFT',  // Matches enum values
            'submitted_by' => null,  // Nullable until submitted
            'submitted_at' => null,
            'rule_template_id' => null,  // Optional workflow template
            'meta' => null,  // Optional JSON metadata
        ];
    }

    public function approved(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'APPROVED',
        ]);
    }

    public function rejected(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'REJECTED',
        ]);
    }

    public function atStep(int $step): self
    {
        return $this->state(fn (array $attributes) => [
            'current_step' => $step,
        ]);
    }
}
