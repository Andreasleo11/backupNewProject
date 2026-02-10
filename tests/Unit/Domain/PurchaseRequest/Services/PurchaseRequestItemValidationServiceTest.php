<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\PurchaseRequest\Services;

use App\Domain\PurchaseRequest\Services\PurchaseRequestItemValidationService;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\DetailPurchaseRequest;
use App\Models\PurchaseRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseRequestItemValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequestItemValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PurchaseRequestItemValidationService;
    }

    public function test_all_items_reviewed_returns_true_when_all_items_approved_by_head(): void
    {
        $pr = $this->createPrWithItems([
            ['is_approve_by_head' => true],
            ['is_approve_by_head' => true],
        ]);

        $result = $this->service->allItemsReviewed($pr, 'head');

        $this->assertTrue($result);
    }

    public function test_all_items_reviewed_returns_true_when_all_items_rejected_by_head(): void
    {
        $pr = $this->createPrWithItems([
            ['is_approve_by_head' => false],
            ['is_approve_by_head' => false],
        ]);

        $result = $this->service->allItemsReviewed($pr, 'head');

        $this->assertTrue($result);
    }

    public function test_all_items_reviewed_returns_true_when_items_have_mixed_approvals(): void
    {
        $pr = $this->createPrWithItems([
            ['is_approve_by_head' => true],
            ['is_approve_by_head' => false],
            ['is_approve_by_head' => true],
        ]);

        $result = $this->service->allItemsReviewed($pr, 'head');

        $this->assertTrue($result);
    }

    public function test_all_items_reviewed_returns_false_when_some_items_pending(): void
    {
        $pr = $this->createPrWithItems([
            ['is_approve_by_head' => true],
            ['is_approve_by_head' => null],
        ]);

        $result = $this->service->allItemsReviewed($pr, 'head');

        $this->assertFalse($result);
    }

    public function test_has_approved_items_returns_true_when_at_least_one_approved(): void
    {
        $pr = $this->createPrWithItems([
            ['is_approve_by_verificator' => true],
            ['is_approve_by_verificator' => false],
        ]);

        $result = $this->service->hasApprovedItems($pr, 'verificator');

        $this->assertTrue($result);
    }

    public function test_has_approved_items_returns_false_when_all_rejected(): void
    {
        $pr = $this->createPrWithItems([
            ['is_approve' => false],
            ['is_approve' => false],
        ]);

        $result = $this->service->hasApprovedItems($pr, 'director');

        $this->assertFalse($result);
    }

    public function test_get_item_stats_returns_correct_counts(): void
    {
        $pr = $this->createPrWithItems([
            ['is_approve_by_head' => true],
            ['is_approve_by_head' => true],
            ['is_approve_by_head' => false],
            ['is_approve_by_head' => null],
            ['is_approve_by_head' => null],
        ]);

        $stats = $this->service->getItemStats($pr, 'head');

        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(2, $stats['approved']);
        $this->assertEquals(1, $stats['rejected']);
        $this->assertEquals(2, $stats['pending']);
    }

    public function test_can_review_items_returns_true_for_dept_head_at_correct_step(): void
    {
        $user = User::factory()->create(['is_head' => 1]);
        $pr = $this->createPrWithWorkflow('pr-dept-head-office', 1);

        $result = $this->service->canReviewItems($user, $pr);

        $this->assertTrue($result);
    }

    public function test_can_review_items_returns_false_for_non_head_at_head_step(): void
    {
        $user = User::factory()->create(['is_head' => 0]);
        $pr = $this->createPrWithWorkflow('pr-dept-head-office', 1);

        $result = $this->service->canReviewItems($user, $pr);

        $this->assertFalse($result);
    }

    public function test_can_review_items_returns_false_at_wrong_workflow_step(): void
    {
        $user = User::factory()->create(['is_head' => 1]);
        // PR is at step 2 (verificator), but user is dept head
        $pr = $this->createPrWithWorkflow('pr-verificator', 2);

        $result = $this->service->canReviewItems($user, $pr);

        $this->assertFalse($result);
    }

    public function test_validate_for_pr_approval_fails_when_items_pending(): void
    {
        $pr = $this->createPrWithItems([
            ['is_approve_by_head' => null],
        ]);

        $result = $this->service->validateForPrApproval($pr, 'head');

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('must approve or reject', $result->getMessage());
    }

    public function test_validate_for_pr_approval_fails_when_all_items_rejected(): void
    {
        $pr = $this->createPrWithItems([
            ['is_approve' => false],
            ['is_approve' => false],
        ]);

        $result = $this->service->validateForPrApproval($pr, 'director');

        $this->assertFalse($result->isValid());
        $this->assertStringContainsString('all items were rejected', $result->getMessage());
    }

    public function test_validate_for_pr_approval_succeeds_when_items_reviewed_and_some_approved(): void
    {
        $pr = $this->createPrWithItems([
            ['is_approve' => true],
            ['is_approve' => false],
        ]);

        $result = $this->service->validateForPrApproval($pr, 'director');

        $this->assertTrue($result->isValid());
    }

    public function test_get_approver_type_from_step_returns_correct_type_for_dept_head(): void
    {
        $step = ApprovalStep::factory()->create(['approver_snapshot_role_slug' => 'pr-dept-head-office']);

        $type = $this->service->getApproverTypeFromStep($step);

        $this->assertEquals('head', $type);
    }

    public function test_get_approver_type_from_step_returns_correct_type_for_verificator(): void
    {
        $step = ApprovalStep::factory()->create(['approver_snapshot_role_slug' => 'pr-verificator']);

        $type = $this->service->getApproverTypeFromStep($step);

        $this->assertEquals('verificator', $type);
    }

    public function test_get_approver_type_from_step_returns_correct_type_for_director(): void
    {
        $step = ApprovalStep::factory()->create(['approver_snapshot_role_slug' => 'pr-director']);

        $type = $this->service->getApproverTypeFromStep($step);

        $this->assertEquals('director', $type);
    }

    public function test_get_approver_type_from_step_returns_null_for_other_steps(): void
    {
        $step = ApprovalStep::factory()->create(['approver_snapshot_role_slug' => 'pr-accounting']);

        $type = $this->service->getApproverTypeFromStep($step);

        $this->assertNull($type);
    }

    // Helper methods

    private function createPrWithItems(array $itemsData): PurchaseRequest
    {
        $pr = PurchaseRequest::factory()->create();

        foreach ($itemsData as $itemData) {
            DetailPurchaseRequest::factory()->create(
                array_merge(['purchase_request_id' => $pr->id], $itemData)
            );
        }

        return $pr->fresh(['details']);
    }

    private function createPrWithWorkflow(string $roleSlug, int $currentStep): PurchaseRequest
    {
        $pr = PurchaseRequest::factory()->create();

        $approval = ApprovalRequest::factory()->create([
            'approvable_id' => $pr->id,
            'approvable_type' => PurchaseRequest::class,
            'current_step' => $currentStep,
        ]);

        ApprovalStep::factory()->create([
            'approval_request_id' => $approval->id,
            'sequence' => $currentStep,
            'approver_snapshot_role_slug' => $roleSlug,
        ]);

        return $pr->fresh(['approvalRequest', 'approvalRequest.steps']);
    }
}
