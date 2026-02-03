<?php

declare(strict_types=1);

namespace Tests\Feature\PurchaseRequest;

use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\DetailPurchaseRequest;
use App\Models\PurchaseRequest;
use App\Models\Specification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemApprovalAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_dept_head_can_approve_item_at_correct_step(): void
    {
        $user = User::factory()->create(['is_head' => 1]);
        $pr = $this->createPrWithWorkflow('pr-dept-head-office', 1);
        $item = DetailPurchaseRequest::factory()->create([
            'purchase_request_id' => $pr->id,
            'is_approve_by_head' => null,
        ]);

        $response = $this->actingAs($user)->post(
            route('purchase-requests.items.approve', $item)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(1, $item->fresh()->is_approve_by_head);
    }

    public function test_dept_head_can_reject_item_at_correct_step(): void
    {
        $user = User::factory()->create(['is_head' => 1]);
        $pr = $this->createPrWithWorkflow('pr-dept-head-office', 1);
        $item = DetailPurchaseRequest::factory()->create([
            'purchase_request_id' => $pr->id,
            'is_approve_by_head' => null,
        ]);

        $response = $this->actingAs($user)->post(
            route('purchase-requests.items.reject', $item)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(0, $item->fresh()->is_approve_by_head);
    }

    public function test_verificator_can_approve_item_at_correct_step(): void
    {
        $spec = Specification::factory()->create(['name' => 'VERIFICATOR']);
        $user = User::factory()->create(['specification_id' => $spec->id]);
        $pr = $this->createPrWithWorkflow('pr-verificator-computer', 2);
        $item = DetailPurchaseRequest::factory()->create([
            'purchase_request_id' => $pr->id,
            'is_approve_by_verificator' => null,
        ]);

        $response = $this->actingAs($user)->post(
            route('purchase-requests.items.approve', $item)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(1, $item->fresh()->is_approve_by_verificator);
    }

    public function test_director_can_approve_item_at_correct_step(): void
    {
        $spec = Specification::factory()->create(['name' => 'DIRECTOR']);
        $user = User::factory()->create(['specification_id' => $spec->id]);
        $pr = $this->createPrWithWorkflow('pr-director', 3);
        $item = DetailPurchaseRequest::factory()->create([
            'purchase_request_id' => $pr->id,
            'is_approve' => null,
        ]);

        $response = $this->actingAs($user)->post(
            route('purchase-requests.items.approve', $item)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(1, $item->fresh()->is_approve);
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = User::factory()->create(['is_head' => 0]);
        $pr = $this->createPrWithWorkflow('pr-dept-head-office', 1);
        $item = DetailPurchaseRequest::factory()->create(['purchase_request_id' => $pr->id]);

        $response = $this->actingAs($user)->post(
            route('purchase-requests.items.approve', $item)
        );

        $response->assertForbidden();
    }

    public function test_wrong_workflow_step_gets_403(): void
    {
        $spec = Specification::factory()->create(['name' => 'DIRECTOR']);
        $user = User::factory()->create(['specification_id' => $spec->id]);
        // PR at step 1 (dept head), but user is director
        $pr = $this->createPrWithWorkflow('pr-dept-head-office', 1);
        $item = DetailPurchaseRequest::factory()->create(['purchase_request_id' => $pr->id]);

        $response = $this->actingAs($user)->post(
            route('purchase-requests.items.approve', $item)
        );

        $response->assertForbidden();
    }

    public function test_get_request_returns_405(): void
    {
        $user = User::factory()->create(['is_head' => 1]);
        $item = DetailPurchaseRequest::factory()->create();

        $response = $this->actingAs($user)->get(
            route('purchase-requests.items.approve.deprecated', ['id' => $item->id])
        );

        $response->assertStatus(405);
        $response->assertSee('Please use POST method');
    }

    public function test_post_without_csrf_fails(): void
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $user = User::factory()->create(['is_head' => 1]);
        $pr = $this->createPrWithWorkflow('pr-dept-head-office', 1);
        $item = DetailPurchaseRequest::factory()->create(['purchase_request_id' => $pr->id]);

        // Re-enable CSRF for this specific request
        $this->withMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $response = $this->actingAs($user)
            ->post(route('purchase-requests.items.approve', $item));

        $response->assertStatus(419); // CSRF token mismatch
    }

    // Helper method

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
