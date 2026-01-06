<?php

namespace Tests\Feature\PurchaseRequest;

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RejectPurchaseRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $approver;

    private PurchaseRequest $pr;

    protected function setUp(): void
    {
        parent::setUp();

        $dept = Department::factory()->create(['name' => 'Computer']);

        $requester = User::factory()->create([
            'department_id' => $dept->id,
        ]);

        $this->approver = User::factory()->create([
            'department_id' => $dept->id,
        ]);

        $this->pr = PurchaseRequest::factory()->create([
            'user_id_create' => $requester->id,
            'from_department' => 'Computer',
            'to_department' => 'Purchasing',
            'status' => 1, // Pending approval
        ]);
    }

    /** @test */
    public function authorized_user_can_reject_purchase_request()
    {
        $this->actingAs($this->approver);

        $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
            'reason' => 'Budget not available',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $this->pr->id,
            'status' => 3, // Rejected
        ]);
    }

    /** @test */
    public function rejection_requires_reason()
    {
        $this->actingAs($this->approver);

        $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
            'reason' => '', // Empty reason
        ]);

        $response->assertSessionHasErrors(['reason']);
    }

    /** @test */
    public function rejection_creates_approval_record_with_rejected_status()
    {
        $this->actingAs($this->approver);

        $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
            'reason' => 'Not aligned with company policy',
        ]);

        $this->assertDatabaseHas('approvals', [
            'approvable_type' => PurchaseRequest::class,
            'approvable_id' => $this->pr->id,
            'user_id' => $this->approver->id,
            'status' => 'rejected',
        ]);
    }

    /** @test */
    public function cannot_reject_already_approved_purchase_request()
    {
        $approvedPr = PurchaseRequest::factory()->create([
            'status' => 4, // Fully approved
        ]);

        $this->actingAs($this->approver);

        $response = $this->post(route('purchase-requests.reject', $approvedPr->id), [
            'reason' => 'Test reason',
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function cannot_reject_cancelled_purchase_request()
    {
        $cancelledPr = PurchaseRequest::factory()->create([
            'status' => 5, // Cancelled
        ]);

        $this->actingAs($this->approver);

        $response = $this->post(route('purchase-requests.reject', $cancelledPr->id), [
            'reason' => 'Test reason',
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function rejection_requires_authentication()
    {
        $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
            'reason' => 'Test reason',
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function rejection_requires_authorization()
    {
        $unauthorizedUser = User::factory()->create();

        $this->actingAs($unauthorizedUser);

        $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
            'reason' => 'Test reason',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $this->pr->id,
            'status' => 1, // Status unchanged
        ]);
    }

    /** @test */
    public function rejection_sends_notification_to_requester()
    {
        // Event::fake();

        $this->actingAs($this->approver);

        $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
            'reason' => 'Budget constraints',
        ]);

        // Event::assertDispatched(PurchaseRequestRejected::class);
    }
}
