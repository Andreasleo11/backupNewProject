<?php

namespace Database\Factories;

use App\Enums\ToDepartment;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseRequest>
 */
class PurchaseRequestFactory extends Factory
{
    protected $model = PurchaseRequest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id_create' => User::factory(),
            'date_pr' => $this->faker->date(),
            'date_required' => $this->faker->date(),
            'remark' => $this->faker->sentence(),
            'to_department' => $this->faker->randomElement(ToDepartment::cases()),
            'status' => 1, // Default pending
            'workflow_status' => 'DRAFT',
            'pr_no' => $this->faker->unique()->numerify('PR-####'),
            'doc_num' => $this->faker->unique()->numerify('DOC-###'),
            'supplier' => $this->faker->company(),
            'pic' => $this->faker->name(),
            'type' => 'office',
            'from_department' => 'Computer', // Default, override as needed
            'branch' => 'JAKARTA',
            'is_import' => false,
            'is_cancel' => false,
        ];
    }

    /**
     * Create PR with full approval workflow.
     *
     * Best practice: Use descriptive method names and chainable states.
     *
     * @param int $currentStep The current approval step (1-4)
     * @param string $type 'office' or 'factory'
     */
    public function withApprovalWorkflow(int $currentStep = 1, string $type = 'office'): static
    {
        return $this->state(fn (array $attributes) => [
            'workflow_status' => 'IN_REVIEW',
            'status' => 1,
            'type' => $type,
        ])->afterCreating(function (PurchaseRequest $pr) use ($currentStep) {
            $type = $pr->type;
            $totalSteps = $this->getTotalSteps($type);

            $approval = ApprovalRequest::factory()->create([
                'approvable_id' => $pr->id,
                'approvable_type' => PurchaseRequest::class,
                'current_step' => $currentStep,
                'status' => 'IN_REVIEW',
                'submitted_by' => $pr->user_id_create,
                'submitted_at' => now(),
            ]);

            $this->createApprovalSteps($approval, $type, $currentStep);

            // Reload the relationship
            $pr->load('approvalRequest', 'approvalRequest.steps');
        });
    }

    /**
     * Convenience states for common workflow steps
     */
    public function atDeptHeadStep(?int $approverId = null): static
    {
        return $this->withApprovalWorkflow(1, 'office', $approverId);
    }

    public function atVerificatorStep(?int $approverId = null): static
    {
        return $this->withApprovalWorkflow(2, 'office', $approverId);
    }

    public function atGmStep(): static
    {
        return $this->state(['type' => 'factory'])
            ->withApprovalWorkflow(3, 'factory');
    }

    public function atDirectorStep(): static
    {
        return $this->withApprovalWorkflow(3, 'office'); // Step 3 for office, step 4 for factory
    }

    /**
     * Create an already-approved PR
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'workflow_status' => 'APPROVED',
            'status' => 4,
            'approved_at' => now(),
        ])->afterCreating(function (PurchaseRequest $pr) {
            if ($pr->approvalRequest) {
                $pr->approvalRequest->update([
                    'status' => 'APPROVED',
                ]);

                // Mark all steps as approved
                $pr->approvalRequest->steps()->update([
                    'status' => 'APPROVED',
                    'acted_at' => now(),
                    'acted_by' => 1,
                ]);
            }
        });
    }

    /**
     * Create a rejected PR
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'workflow_status' => 'REJECTED',
            'status' => 5,
        ])->afterCreating(function (PurchaseRequest $pr) {
            if ($pr->approvalRequest) {
                $pr->approvalRequest->update([
                    'status' => 'REJECTED',
                ]);
            }
        });
    }

    /**
     * Create a cancelled PR
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_cancel' => true,
            'workflow_status' => 'CANCELLED',
        ]);
    }

    /**
     * Helper: Get total steps based on PR type
     */
    private function getTotalSteps(string $type): int
    {
        return $type === 'factory' ? 4 : 3; // Factory has GM step
    }

    /**
     * Helper: Create approval steps for workflow
     */
    private function createApprovalSteps(ApprovalRequest $approval, string $type, int $currentStep, ?int $userApproverId = null): void
    {
        $steps = $this->getStepsConfiguration($type);

        foreach ($steps as $sequence => $roleId) {
            // For tests with user approver, create user-based approval instead of role-based
            if ($userApproverId && $sequence === $currentStep) {
                ApprovalStep::factory()->create([
                    'approval_request_id' => $approval->id,
                    'sequence' => $sequence,
                    'approver_type' => 'user',
                    'approver_id' => $userApproverId,  // Specific user for testing
                    'status' => $sequence < $currentStep ? 'APPROVED' : 'PENDING',
                    'acted_at' => $sequence < $currentStep ? now() : null,
                    'acted_by' => $sequence < $currentStep ? $userApproverId : null,
                ]);
            } else {
                ApprovalStep::factory()->create([
                    'approval_request_id' => $approval->id,
                    'sequence' => $sequence,
                    'approver_type' => 'role',
                    'approver_id' => $roleId,  // This should be actual role ID, not slug
                    'status' => $sequence < $currentStep ? 'APPROVED' : 'PENDING',
                    'acted_at' => $sequence < $currentStep ? now() : null,
                    'acted_by' => $sequence < $currentStep ? 1 : null,
                ]);
            }
        }
    }

    /**
     * Helper: Get step configuration by PR type
     */
    private function getStepsConfiguration(string $type): array
    {
        // Use actual test role IDs from TestRoleSeeder
        // Role ID 100 = pr-dept-head-office
        // Role ID 102 = pr-verificator-computer
        // Role ID 104 = pr-gm
        // Role ID 105 = pr-director
        $baseSteps = [
            1 => 100,  // pr-dept-head-office
            2 => 102,  // pr-verificator-computer
        ];

        if ($type === 'factory') {
            $baseSteps[3] = 104;  // pr-gm
            $baseSteps[4] = 105;  // pr-director
        } else {
            $baseSteps[3] = 105;  // pr-director
        }

        return $baseSteps;
    }

    /**
     * Helper: Get human-readable role name from slug
     */
    private function getRoleName(string $roleSlug): string
    {
        $roleNames = [
            'pr-dept-head-office' => 'Dept Head - Office',
            'pr-dept-head-factory' => 'Dept Head - Factory',
            'pr-verificator-computer' => 'Verificator - Computer',
            'pr-verificator-personalia' => 'Verificator - Personalia',
            'pr-gm' => 'General Manager',
            'pr-director' => 'Director',
        ];

        return $roleNames[$roleSlug] ?? $roleSlug;
    }
}
