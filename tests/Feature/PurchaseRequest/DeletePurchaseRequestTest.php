<?php

namespace Tests\Feature\PurchaseRequest;

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DeletePurchaseRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private PurchaseRequest $draftPr;

    protected function setUp(): void
    {
        parent::setUp();

        $dept = Department::factory()->create(['name' => 'Computer']);

        $this->user = User::factory()->create([
            'department_id' => $dept->id,
        ]);

        $this->actingAs($this->user);

        $this->draftPr = PurchaseRequest::factory()->create([
            'user_id_create' => $this->user->id,
            'from_department' => 'Computer',
            'to_department' => 'Purchasing',
            'status' => 8, // Draft
        ]);
    }

    /** @test */
    public function it_can_delete_draft_purchase_request()
    {
        $response = $this->delete(route('purchase-requests.destroy', $this->draftPr->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('purchase_requests', [
            'id' => $this->draftPr->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_non_draft_purchase_request()
    {
        $submittedPr = PurchaseRequest::factory()->create([
            'user_id_create' => $this->user->id,
            'status' => 1, // Submitted, not draft
        ]);

        $response = $this->delete(route('purchase-requests.destroy', $submittedPr->id));

        $response->assertForbidden();

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $submittedPr->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function it_cannot_delete_approved_purchase_request()
    {
        $approvedPr = PurchaseRequest::factory()->create([
            'user_id_create' => $this->user->id,
            'status' => 4, // Approved
        ]);

        $response = $this->delete(route('purchase-requests.destroy', $approvedPr->id));

        $response->assertForbidden();
    }

    /** @test */
    public function user_can_only_delete_their_own_purchase_requests()
    {
        $otherUser = User::factory()->create();
        $otherDraftPr = PurchaseRequest::factory()->create([
            'user_id_create' => $otherUser->id,
            'status' => 8, // Draft
        ]);

        $response = $this->delete(route('purchase-requests.destroy', $otherDraftPr->id));

        $response->assertForbidden();

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $otherDraftPr->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function deletion_requires_authentication()
    {
        Auth::logout(); // Logout for this test specifically
        $response = $this->delete(route('purchase-requests.destroy', $this->draftPr->id));

        $response->assertRedirect(route('login'));

        // Re-login for tearDown/subsequent tests if needed, though setUp handles it
        $this->actingAs($this->user);
    }

    /** @test */
    public function deleting_purchase_request_also_soft_deletes_items()
    {
        // Create items for the PR
        $this->draftPr->items()->create([
            'item_name' => 'Test Item',
            'quantity' => 10,
            'uom' => 'PCS',
            'price' => 100,
            'currency' => 'IDR',
            'purpose' => 'Testing',
        ]);

        $response = $this->delete(route('purchase-requests.destroy', $this->draftPr->id));

        $this->assertSoftDeleted('purchase_requests', [
            'id' => $this->draftPr->id,
        ]);

        // Items should also be soft deleted if cascading is set up
        $this->assertSoftDeleted('detail_purchase_requests', [
            'purchase_request_id' => $this->draftPr->id,
        ]);
    }
}
