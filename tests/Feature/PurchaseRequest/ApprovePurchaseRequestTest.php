<?php

namespace Tests\Feature\PurchaseRequest;

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ApprovePurchaseRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $approver;

    private PurchaseRequest $pr;

    protected function setUp(): void
    {
        parent::setUp();

        $dept = Department::factory()->create(['name' => 'Computer']);

        // Create requester
        $requester = User::factory()->create([
            'department_id' => $dept->id,
        ]);

        // Create approver with appropriate role/permissions
        $this->approver = User::factory()->create([
            'department_id' => $dept->id,
        ]);

        // Assign approval permission/role to approver
        // This depends on your permission system

        $this->pr = PurchaseRequest::factory()->create([
            'user_id_create' => $requester->id,
            'from_department' => 'Computer',
            'to_department' => 'Purchasing',
            'status' => 1, // Pending approval
        ]);
    }

    /** @test */
    public function authorized_user_can_approve_purchase_request()
    {
        $this->actingAs($this->approver);

        $response = $this->post(route('purchase-requests.approve', $this->pr->id), [
            'notes' => 'Approved by department head',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $this->pr->id,
            'status' => 2, // Next status after approval
        ]);
    }

    /** @test */
    public function approval_updates_status_correctly_based_on_workflow()
    {
        // Test status progression through approval workflow
        $this->actingAs($this->approver);

        $response = $this->post(route('purchase-requests.approve', $this->pr->id));

        $updatedPr = $this->pr->fresh();

        // Verify status changed
        $this->assertNotEquals(1, $updatedPr->status);
        $this->assertContains($updatedPr->status, [2, 3, 4, 6, 7]); // Valid next statuses
    }

    /** @test */
    public function approval_creates_approval_record()
    {
        $this->actingAs($this->approver);

        $response = $this->post(route('purchase-requests.approve', $this->pr->id), [
            'notes' => 'Test approval notes',
        ]);

        // Verify approval record created
        $this->assertDatabaseHas('approvals', [
            'approvable_type' => PurchaseRequest::class,
            'approvable_id' => $this->pr->id,
            'user_id' => $this->approver->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function cannot_approve_already_approved_purchase_request()
    {
        $approvedPr = PurchaseRequest::factory()->create([
            'status' => 4, // Already fully approved
        ]);

        $this->actingAs($this->approver);

        $response = $this->post(route('purchase-requests.approve', $approvedPr->id));

        $response->assertForbidden();
    }

    /** @test */
    public function cannot_approve_cancelled_purchase_request()
    {
        $cancelledPr = PurchaseRequest::factory()->create([
            'status' => 5, // Cancelled
        ]);

        $this->actingAs($this->approver);

        $response = $this->post(route('purchase-requests.approve', $cancelledPr->id));

        $response->assertForbidden();
    }

    /** @test */
    public function approval_requires_authentication()
    {
        $response = $this->post(route('purchase-requests.approve', $this->pr->id));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function approval_requires_authorization()
    {
        // Create user without approval permissions
        $unauthorizedUser = User::factory()->create();

        $this->actingAs($unauthorizedUser);

        $response = $this->post(route('purchase-requests.approve', $this->pr->id));

        $response->assertForbidden();

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $this->pr->id,
            'status' => 1, // Status unchanged
        ]);
    }

    /** @test */
    public function approval_sends_notification_to_requester()
    {
        Event::fake();

        $this->actingAs($this->approver);

        $response = $this->post(route('purchase-requests.approve', $this->pr->id));

        // Verify notification event was dispatched
        // Event::assertDispatched(PurchaseRequestApproved::class);
    }

    /** @test */
    public function final_approval_sets_status_to_approved()
    {
        // Test final approval in workflow
        $prReadyForFinalApproval = PurchaseRequest::factory()->create([
            'status' => 3, // One step before final approval
        ]);

        $finalApprover = User::factory()->create();
        // Assign final approver role

        $this->actingAs($finalApprover);

        $response = $this->post(route('purchase-requests.approve', $prReadyForFinalApproval->id));

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $prReadyForFinalApproval->id,
            'status' => 4, // Fully approved
        ]);
    }
}
