<?php

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test user with department
    $this->fromDept = Department::factory()->create([
        'name' => 'Computer',
        'is_office' => true,
    ]);

    $this->toDept = Department::factory()->create([
        'name' => 'Purchasing',
        'is_office' => true,
    ]);

    $this->user = User::factory()->create([
        'department_id' => $this->fromDept->id,
    ]);
});

test('it can create a purchase request successfully', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('purchase-requests.store'), [
        'from_department' => 'Computer',
        'to_department' => 'Purchasing',
        'branch' => 'JAKARTA',
        'date_of_pr' => now()->format('Y-m-d'),
        'date_of_required' => now()->addDays(7)->format('Y-m-d'),
        'remark' => 'Test purchase request',
        'supplier' => 'Test Supplier',
        'pic' => 'Test PIC',
        'is_draft' => false,
        'items' => [
            [
                'item_name' => 'Test Item 1',
                'quantity' => 10,
                'uom' => 'PCS',
                'price' => 100.50,
                'currency' => 'IDR',
                'purpose' => 'Testing',
            ],
            [
                'item_name' => 'Test Item 2',
                 'quantity' => 5,
                'uom' => 'PCS',
                'price' => 200.00,
                'currency' => 'IDR',
                'purpose' => 'Testing',
            ],
        ],
    ]);

    $response->assertRedirect(route('purchase-requests.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('purchase_requests', [
        'user_id_create' => $this->user->id,
        'from_department' => 'Computer',
        'to_department' => 'Purchasing',
        'branch' => 'JAKARTA',
        'status' => 1, // Pending Department Head
        'type' => 'office',
    ]);

    $pr = PurchaseRequest::latest()->first();
    expect($pr->doc_num)->not->toBeNull();
    expect($pr->pr_no)->not->toBeNull();

    // Verify items were created
    $this->assertDatabaseHas('detail_purchase_requests', [
        'purchase_request_id' => $pr->id,
        'item_name' => 'Test Item 1',
        'quantity' => 10,
    ]);

    $this->assertDatabaseHas('detail_purchase_requests', [
        'purchase_request_id' => $pr->id,
        'item_name' => 'Test Item 2',
        'quantity' => 5,
    ]);
});

test('it creates draft purchase request with status 8', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('purchase-requests.store'), [
        'from_department' => 'Computer',
        'to_department' => 'Purchasing',
        'branch' => 'JAKARTA',
        'date_of_pr' => now()->format('Y-m-d'),
        'date_of_required' => now()->addDays(7)->format('Y-m-d'),
        'remark' => 'Draft PR',
        'supplier' => 'Test Supplier',
        'pic' => 'Test PIC',
        'is_draft' => true, // Draft mode
        'items' => [
            [
                'item_name' => 'Draft Item',
                'quantity' => 1,
                'uom' => 'PCS',
                'price' => 50,
                'currency' => 'IDR',
                'purpose' => 'Testing draft',
            ],
        ],
    ]);

    $this->assertDatabaseHas('purchase_requests', [
        'user_id_create' => $this->user->id,
        'status' => 8, // Draft status
    ]);
});

test('it generates unique document numbers', function () {
    $this->actingAs($this->user);

    // Create first PR
    $this->post(route('purchase-requests.store'), [
        'from_department' => 'Computer',
        'to_department' => 'Purchasing',
        'branch' => 'JAKARTA',
        'date_of_pr' => now()->format('Y-m-d'),
        'date_of_required' => now()->addDays(7)->format('Y-m-d'),
        'remark' => 'First PR',
        'supplier' => 'Test Supplier',
        'pic' => 'Test PIC',
        'is_draft' => false,
        'items' => [
            [
                'item_name' => 'Item 1',
                'quantity' => 1,
                'uom' => 'PCS',
                'price' => 100,
                'currency' => 'IDR',
                'purpose' => 'Testing',
            ],
        ],
    ]);

    // Create second PR
    $this->post(route('purchase-requests.store'), [
        'from_department' => 'Computer',
        'to_department' => 'Purchasing',
        'branch' => 'JAKARTA',
        'date_of_pr' => now()->format('Y-m-d'),
        'date_of_required' => now()->addDays(7)->format('Y-m-d'),
        'remark' => 'Second PR',
        'supplier' => 'Test Supplier 2',
        'pic' => 'Test PIC 2',
        'is_draft' => false,
        'items' => [
            [
                'item_name' => 'Item 2',
                'quantity' => 2,
                'uom' => 'PCS',
                'price' => 200,
                'currency' => 'IDR',
                'purpose' => 'Testing',
            ],
        ],
    ]);

    $prs = PurchaseRequest::latest()->take(2)->get();

    // Verify doc_nums are different
    expect($prs[0]->doc_num)->not->toEqual($prs[1]->doc_num);

    // Verify pr_nos are different
    expect($prs[0]->pr_no)->not->toEqual($prs[1]->pr_no);
});

test('it validates required fields', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('purchase-requests.store'), [
        // Missing required fields
    ]);

    $response->assertSessionHasErrors([
        'from_department',
        'to_department',
        'date_of_pr',
        'date_of_required',
    ]);
});

test('it requires at least one item', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('purchase-requests.store'), [
        'from_department' => 'Computer',
        'to_department' => 'Purchasing',
        'branch' => 'JAKARTA',
        'date_of_pr' => now()->format('Y-m-d'),
        'date_of_required' => now()->addDays(7)->format('Y-m-d'),
        'remark' => 'Test',
        'supplier' => 'Test Supplier',
        'pic' => 'Test PIC',
        'is_draft' => false,
        'items' => [], // No items
    ]);

    $response->assertSessionHasErrors(['items']);
});

test('it sets correct status for plastic injection department', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('purchase-requests.store'), [
        'from_department' => 'Plastic Injection',
        'to_department' => 'Purchasing',
        'branch' => 'JAKARTA',
        'date_of_pr' => now()->format('Y-m-d'),
        'date_of_required' => now()->addDays(7)->format('Y-m-d'),
        'remark' => 'Test',
        'supplier' => 'Test Supplier',
        'pic' => 'Test PIC',
        'is_draft' => false,
        'items' => [
            [
                'item_name' => 'Test Item',
                'quantity' => 1,
                'uom' => 'PCS',
                'price' => 100,
                'currency' => 'IDR',
                'purpose' => 'Testing',
            ],
        ],
    ]);

    // Plastic Injection should go directly to GM (status 7)
    $this->assertDatabaseHas('purchase_requests', [
        'from_department' => 'Plastic Injection',
        'status' => 7,
    ]);
});

test('it sets correct status for personalia department', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('purchase-requests.store'), [
        'from_department' => 'Personalia',
        'to_department' => 'Purchasing',
        'branch' => 'JAKARTA',
        'date_of_pr' => now()->format('Y-m-d'),
        'date_of_required' => now()->addDays(7)->format('Y-m-d'),
        'remark' => 'Test',
        'supplier' => 'Test Supplier',
        'pic' => 'Test PIC',
        'is_draft' => false,
        'items' => [
            [
                'item_name' => 'Test Item',
                'quantity' => 1,
                'uom' => 'PCS',
                'price' => 100,
                'currency' => 'IDR',
                'purpose' => 'Testing',
            ],
        ],
    ]);

    // Personalia should go directly to Purchaser (status 6)
    $this->assertDatabaseHas('purchase_requests', [
        'from_department' => 'Personalia',
        'status' => 6,
    ]);
});
