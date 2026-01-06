<?php

namespace Tests\Feature\PurchaseRequest;

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CancelPurchaseRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private PurchaseRequest $pr;

    protected function setUp(): void
    {
        parent::setUp();

        $dept = Department::factory()->create(['name' => 'Computer']);

        $this->user = User::factory()->create([
            'department_id' => $dept->id,
        ]);

        $this->pr = PurchaseRequest::factory()->create([
            'user_id_create' => $this->user->id,
            'from_department' => 'Computer',
            'to_department' => 'Purchasing',
            'status' => 1, // Pending
        ]);
    }

    /** @test */
    public function it_can_cancel_pending_purchase_request()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('purchase-requests.cancel', $this->pr->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $this->pr->id,
            'status' => 5, // Cancelled
        ]);
    }

    /** @test */
    public function it_cannot_cancel_approved_purchase_request()
    {
        $approvedPr = PurchaseRequest::factory()->create([
            'user_id_create' => $this->user->id,
            'status' => 4, // Approved
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('purchase-requests.cancel', $approvedPr->id));

        $response->assertForbidden();

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $approvedPr->id,
            'status' => 4, // Still approved
        ]);
    }

    /** @test */
    public function it_cannot_cancel_already_cancelled_purchase_request()
    {
        $cancelledPr = PurchaseRequest::factory()->create([
            'user_id_create' => $this->user->id,
            'status' => 5, // Already cancelled
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('purchase-requests.cancel', $cancelledPr->id));

        $response->assertForbidden();
    }

    /** @test */
    public function user_can_only_cancel_their_own_purchase_requests()
    {
        $otherUser = User::factory()->create();
        $otherPr = PurchaseRequest::factory()->create([
            'user_id_create' => $otherUser->id,
            'status' => 1,
        ]);

        $this->actingAs($this->user);

        $response = $this->post(route('purchase-requests.cancel', $otherPr->id));

        $response->assertForbidden();

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $otherPr->id,
            'status' => 1, // Status unchanged
        ]);
    }

    /** @test */
    public function cancellation_requires_authentication()
    {
        $response = $this->post(route('purchase-requests.cancel', $this->pr->id));

        $response->assertRedirect(route('login'));
    }
}
