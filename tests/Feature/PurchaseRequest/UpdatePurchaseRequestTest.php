<?php

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;

uses(DatabaseTruncation::class);

beforeEach(function () {
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
});

test('it can update a draft purchase request', function () {
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
        'is_draft' => '1',
        'items' => [
            [
                'item_name' => 'Updated Item',
                'quantity' => 20,
                'uom' => 'PCS',
                'price' => 'Rp 150.00',
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
});

test('it cannot update approved purchase request', function () {
    // ✅ Use approved() factory state
    $approvedPr = PurchaseRequest::factory()
        ->approved()
        ->create([
            'user_id_create' => $this->user->id,
            'from_department' => 'Computer',
            'to_department' => 'Purchasing',
        ]);

    $this->actingAs($this->user);

    $response = $this->put(route('purchase-requests.update', $approvedPr->id), [
        'from_department' => 'Computer',
        'to_department' => 'Purchasing',
        'branch' => 'JAKARTA',
        'date_of_pr' => now()->format('Y-m-d'),
        'date_of_required' => now()->addDays(7)->format('Y-m-d'),
        'supplier' => 'Supplier',
        'pic' => 'PIC',
        'items' => [
            [
                'item_name' => 'Item',
                'quantity' => 1,
                'uom' => 'PCS',
                'price' => '100.00',
                'currency' => 'IDR',
                'purpose' => 'Test',
            ],
        ],
    ]);

    $response->assertForbidden();
});

test('it updates items when updating purchase request', function () {
    $this->actingAs($this->user);

    $response = $this->put(route('purchase-requests.update', $this->pr->id), [
        'from_department' => 'Computer',
        'to_department' => 'Purchasing',
        'branch' => 'JAKARTA',
        'date_of_pr' => now()->format('Y-m-d'),
        'date_of_required' => now()->addDays(7)->format('Y-m-d'),
        'supplier' => 'Supplier',
        'pic' => 'PIC',
        'is_draft' => '1',
        'items' => [
            [
                'item_name' => 'New Item 1',
                'quantity' => 5,
                'uom' => 'PCS',
                'price' => '100.00',
                'currency' => 'IDR',
                'purpose' => 'Testing',
            ],
            [
                'item_name' => 'New Item 2',
                'quantity' => 10,
                'uom' => 'PCS',
                'price' => '200.00',
                'currency' => 'IDR',
                'purpose' => 'Testing',
            ],
        ],
    ]);

    $this->assertDatabaseHas('detail_purchase_requests', [
        'purchase_request_id' => $this->pr->id,
        'item_name' => 'New Item 1',
    ]);

    $this->assertDatabaseHas('detail_purchase_requests', [
        'purchase_request_id' => $this->pr->id,
        'item_name' => 'New Item 2',
    ]);

    expect($this->pr->fresh()->items->count())->toBe(2);
});

test('user can only update their own purchase requests', function () {
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
        'supplier' => 'Supplier',
        'pic' => 'PIC',
        'items' => [
            [
                'item_name' => 'Item',
                'quantity' => 1,
                'uom' => 'PCS',
                'price' => '100.00',
                'currency' => 'IDR',
                'purpose' => 'Test',
            ],
        ],
    ]);

    $response->assertForbidden();
});
