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
            'status' => 'pending',
            'total_steps' => 4,
            'initiated_by_user_id' => 1,
            'initiated_by_user_name' => 'Test User',
            'initiated_at' => now(),
            'completed_at' => null,
        ];
    }

    public function approved(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'completed_at' => now(),
        ]);
    }

    public function rejected(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'completed_at' => now(),
        ]);
    }

    public function atStep(int $step): self
    {
        return $this->state(fn (array $attributes) => [
            'current_step' => $step,
        ]);
    }
}
