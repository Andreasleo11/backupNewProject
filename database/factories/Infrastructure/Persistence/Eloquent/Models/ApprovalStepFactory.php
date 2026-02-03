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
            'approver_snapshot_role_slug' => 'pr-dept-head-office',
            'approver_snapshot_role_name' => 'Dept Head - Office',
            'approver_snapshot_user_id' => null,
            'approver_snapshot_user_name' => null,
            'status' => 'pending',
            'acted_at' => null,
            'acted_by_user_id' => null,
            'acted_by_user_name' => null,
            'remarks' => null,
        ];
    }

    public function withRole(string $roleSlug): self
    {
        $roleNames = [
            'pr-dept-head-office' => 'Dept Head - Office',
            'pr-dept-head-factory' => 'Dept Head - Factory',
            'pr-verificator-computer' => 'Verificator - Computer',
            'pr-verificator-personalia' => 'Verificator - Personalia',
            'pr-director' => 'Director',
        ];

        return $this->state(fn (array $attributes) => [
            'approver_snapshot_role_slug' => $roleSlug,
            'approver_snapshot_role_name' => $roleNames[$roleSlug] ?? $roleSlug,
        ]);
    }

    public function approved(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'acted_at' => now(),
            'acted_by_user_id' => 1,
            'acted_by_user_name' => 'Test User',
        ]);
    }

    public function rejected(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'acted_at' => now(),
            'acted_by_user_id' => 1,
            'acted_by_user_name' => 'Test User',
            'remarks' => 'Rejected for testing',
        ]);
    }
}
