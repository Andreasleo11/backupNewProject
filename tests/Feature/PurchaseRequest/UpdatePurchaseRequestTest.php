<?php

namespace Tests\Feature\PurchaseRequest;

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdatePurchaseRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Department $dept;

    private PurchaseRequest $pr;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dept = Department::factory()->create([
            'name' => 'Computer',
            'is_office' => true,
        ]);

        $this->user = User::factory()->create([
            'department_id' => $this->dept->id,
        ]);

        // Create a draft PR to update
        $this->pr = PurchaseRequest::factory()->create([
            'user_id_create' => $this->user->id,
            'from_department' => 'Computer',
            'to_department' => 'Purchasing',
            'status' => 8, // Draft
            'branch' => 'JAKARTA',
        ]);
    }

    /** @test */
    public function it_can_update_a_draft_purchase_request()
    {
        $this->actingAs($this->user);

        $response = $this->put(route('purchase-requests.update', $this->pr->id), [
            'from_department' => 'Computer',
            'to_department' => 'Maintenance',
            'branch' => 'BANDUNG',
            'date_of_pr' => now()->format('Y-m-d'),
            'date_of_required' => now()->addDays(10)->format('Y-m-d'),
            'remark' => 'Updated remark',
            'supplier' => 'Updated Supplier',
            'pic' => 'Updated PIC',
            'is_draft' => true,
            'items' => [
                [
                    'item_name' => 'Updated Item',
                    'quantity' => 20,
                    'uom' => 'PCS',
                    'price' => 150.00,
                    'currency' => 'IDR',
                    'purpose' => 'Updated purpose',
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $this->pr->id,
            'to_department' => 'Maintenance',
            'branch' => 'BANDUNG',
            'remark' => 'Updated remark',
        ]);
    }

    /** @test */
    public function it_cannot_update_approved_purchase_request()
    {
        $approvedPr = PurchaseRequest::factory()->create([
            'user_id_create' => $this->user->id,
            'from_department' => 'Computer',
            'to_department' => 'Purchasing',
            'status' => 4, // Approved
        ]);

        $this->actingAs($this->user);

        $response = $this->put(route('purchase-requests.update', $approvedPr->id), [
            'from_department' => 'Computer',
            'to_department' => 'Purchasing',
            'branch' => 'JAKARTA',
            'date_of_pr' => now()->format('Y-m-d'),
            'date_of_required' => now()->addDays(7)->format('Y-m-d'),
            'items' => [
                [
                    'item_name' => 'Item',
                    'quantity' => 1,
                    'uom' => 'PCS',
                    'price' => 100,
                    'currency' => 'IDR',
                    'purpose' => 'Test',
                ],
            ],
        ]);

        // Should not allow update
        $response->assertForbidden();
    }

    /** @test */
    public function it_updates_items_when_updating_purchase_request()
    {
        $this->actingAs($this->user);

        $response = $this->put(route('purchase-requests.update', $this->pr->id), [
            'from_department' => 'Computer',
            'to_department' => 'Purchasing',
            'branch' => 'JAKARTA',
            'date_of_pr' => now()->format('Y-m-d'),
            'date_of_required' => now()->addDays(7)->format('Y-m-d'),
            'is_draft' => true,
            'items' => [
                [
                    'item_name' => 'New Item 1',
                    'quantity' => 5,
                    'uom' => 'PCS',
                    'price' => 100,
                    'currency' => 'IDR',
                    'purpose' => 'Testing',
                ],
                [
                    'item_name' => 'New Item 2',
                    'quantity' => 10,
                    'uom' => 'PCS',
                    'price' => 200,
                    'currency' => 'IDR',
                    'purpose' => 'Testing',
                ],
            ],
        ]);

        // Verify new items exist
        $this->assertDatabaseHas('detail_purchase_requests', [
            'purchase_request_id' => $this->pr->id,
            'item_name' => 'New Item 1',
        ]);

        $this->assertDatabaseHas('detail_purchase_requests', [
            'purchase_request_id' => $this->pr->id,
            'item_name' => 'New Item 2',
        ]);

        // Verify count
        $this->assertEquals(2, $this->pr->fresh()->items->count());
    }

    /** @test */
    public function user_can_only_update_their_own_purchase_requests()
    {
        $otherUser = User::factory()->create();
        $otherPr = PurchaseRequest::factory()->create([
            'user_id_create' => $otherUser->id,
            'status' => 8,
        ]);

        $this->actingAs($this->user);

        $response = $this->put(route('purchase-requests.update', $otherPr->id), [
            'from_department' => 'Computer',
            'to_department' => 'Purchasing',
            'branch' => 'JAKARTA',
            'date_of_pr' => now()->format('Y-m-d'),
            'date_of_required' => now()->addDays(7)->format('Y-m-d'),
            'items' => [
                [
                    'item_name' => 'Item',
                    'quantity' => 1,
                    'uom' => 'PCS',
                    'price' => 100,
                    'currency' => 'IDR',
                    'purpose' => 'Test',
                ],
            ],
        ]);

        $response->assertForbidden();
    }
}
