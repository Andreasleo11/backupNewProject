<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\PurchaseRequest\Services;

use App\Domain\PurchaseRequest\Services\ItemApprovalAuthorizationService;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Models\DetailPurchaseRequest;
use App\Models\PurchaseRequest;
use App\Models\Specification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemApprovalAuthorizationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ItemApprovalAuthorizationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ItemApprovalAuthorizationService;
    }

    public function test_dept_head_can_approve_at_step_1(): void
    {
        $user = User::factory()->create(['is_head' => 1]);
        $pr = $this->createPrWithWorkflow('pr-dept-head', 1);
        $item = DetailPurchaseRequest::factory()->create(['purchase_request_id' => $pr->id]);

        $result = $this->service->canApproveOrReject($user, $item);

        $this->assertTrue($result);
    }

    public function test_non_head_cannot_approve_at_dept_head_step(): void
    {
        $user = User::factory()->create(['is_head' => 0]);
        $pr = $this->createPrWithWorkflow('pr-dept-head', 1);
        $item = DetailPurchaseRequest::factory()->create(['purchase_request_id' => $pr->id]);

        $result = $this->service->canApproveOrReject($user, $item);

        $this->assertFalse($result);
    }

    public function test_verificator_can_approve_at_step_2(): void
    {
        $spec = Specification::factory()->create(['name' => 'VERIFICATOR']);
        $user = User::factory()->create(['specification_id' => $spec->id]);
        $pr = $this->createPrWithWorkflow('pr-verificator', 2);
        $item = DetailPurchaseRequest::factory()->create(['purchase_request_id' => $pr->id]);

        $result = $this->service->canApproveOrReject($user, $item);

        $this->assertTrue($result);
    }

    public function test_non_verificator_cannot_approve_at_verificator_step(): void
    {
        $user = User::factory()->create();
        $pr = $this->createPrWithWorkflow('pr-verificator', 2);
        $item = DetailPurchaseRequest::factory()->create(['purchase_request_id' => $pr->id]);

        $result = $this->service->canApproveOrReject($user, $item);

        $this->assertFalse($result);
    }

    public function test_director_can_approve_at_step_3(): void
    {
        $spec = Specification::factory()->create(['name' => 'DIRECTOR']);
        $user = User::factory()->create(['specification_id' => $spec->id]);
        $pr = $this->createPrWithWorkflow('pr-director', 3);
        $item = DetailPurchaseRequest::factory()->create(['purchase_request_id' => $pr->id]);

        $result = $this->service->canApproveOrReject($user, $item);

        $this->assertTrue($result);
    }

    public function test_non_director_cannot_approve_at_director_step(): void
    {
        $user = User::factory()->create();
        $pr = $this->createPrWithWorkflow('pr-director', 3);
        $item = DetailPurchaseRequest::factory()->create(['purchase_request_id' => $pr->id]);

        $result = $this->service->canApproveOrReject($user, $item);

        $this->assertFalse($result);
    }

    public function test_personalia_dept_head_must_match_department(): void
    {
        $personaliaDept = Department::factory()->create(['name' => 'PERSONALIA']);
        $user = User::factory()->create([
            'is_head' => 1,
            'department_id' => $personaliaDept->id,
        ]);

        $pr = $this->createPrWithWorkflow('pr-dept-head', 1);
        $pr->update(['from_department' => 'PERSONALIA']);

        $item = DetailPurchaseRequest::factory()->create(['purchase_request_id' => $pr->id]);

        $result = $this->service->canApproveOrReject($user, $item);

        $this->assertTrue($result);
    }

    public function test_wrong_dept_head_cannot_approve_personalia_pr(): void
    {
        $otherDept = Department::factory()->create(['name' => 'FINANCE']);
        $user = User::factory()->create([
            'is_head' => 1,
            'department_id' => $otherDept->id,
        ]);

        $pr = $this->createPrWithWorkflow('pr-dept-head', 1);
        $pr->update(['from_department' => 'PERSONALIA']);

        $item = DetailPurchaseRequest::factory()->create(['purchase_request_id' => $pr->id]);

        $result = $this->service->canApproveOrReject($user, $item);

        $this->assertFalse($result);
    }

    public function test_cannot_approve_at_accounting_step(): void
    {
        $user = User::factory()->create(['is_head' => 1]);
        $pr = $this->createPrWithWorkflow('pr-accounting', 4);
        $item = DetailPurchaseRequest::factory()->create(['purchase_request_id' => $pr->id]);

        $result = $this->service->canApproveOrReject($user, $item);

        $this->assertFalse($result);
    }

    public function test_returns_false_when_no_approval_request(): void
    {
        $user = User::factory()->create(['is_head' => 1]);
        $pr = PurchaseRequest::factory()->create();
        $item = DetailPurchaseRequest::factory()->create(['purchase_request_id' => $pr->id]);

        $result = $this->service->canApproveOrReject($user, $item);

        $this->assertFalse($result);
    }

    // Helper methods

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
