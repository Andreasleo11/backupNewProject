<?php

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Tests\Database\Seeders\TestRoleSeeder;
use Tests\Helpers\SignatureTestHelper;

uses(DatabaseTruncation::class);

beforeEach(function () {
    // Force clean up to avoid ID conflicts
    \Illuminate\Support\Facades\DB::table('model_has_roles')->delete();
    \Illuminate\Support\Facades\DB::table('roles')->delete();

    $this->seed(TestRoleSeeder::class);
    // Seed DB rules (needed for ApprovalEngine to find a template)
    $this->seed(\Database\Seeders\PrApprovalRulesSeeder::class);

    $this->dept = Department::factory()->create(['name' => 'Computer', 'is_office' => true]);

    $this->user = User::factory()->create([
        'department_id' => $this->dept->id,
    ]);

    // Create default signature for the user (required for sign & submit)
    SignatureTestHelper::createDefaultSignature($this->user->id);
});

test('creator can view edit button for rejected pr', function () {
    $pr = PurchaseRequest::factory()
        ->rejected()
        ->create([
            'user_id_create' => $this->user->id,
            'from_department' => 'Computer',
            'to_department' => 'Purchasing',
        ]);

    $this->actingAs($this->user);

    // Check Policy
    expect($this->user->can('update', $pr))->toBeTrue();

    // Check UI
    $response = $this->get(route('purchase-requests.show', $pr));
    $response->assertSee('Edit Details');
});

test('creator can view sign and submit button for rejected pr', function () {
    $pr = PurchaseRequest::factory()
        ->rejected()
        ->create([
            'user_id_create' => $this->user->id,
            'from_department' => 'Computer',
            'to_department' => 'Purchasing',
        ]);

    $this->actingAs($this->user);

    // Check UI
    $response = $this->get(route('purchase-requests.show', $pr));
    $response->assertSee('Sign & Submit');
});

test('creator can resubmit rejected pr', function () {
    $pr = PurchaseRequest::factory()
        ->rejected()
        ->create([
            'user_id_create' => $this->user->id,
            'from_department' => 'Computer',
            'to_department' => 'Purchasing',
            'branch' => 'JAKARTA',
            'type' => 'office',
        ]);

    // Create items for the PR (needed for approval rule matching)
    \App\Models\DetailPurchaseRequest::factory()->create([
        'purchase_request_id' => $pr->id,
        'quantity' => 10,
        'price' => 1000,
    ]);

    $this->actingAs($this->user);

    $response = $this->post(route('purchase-requests.sign-and-submit', $pr));

    $response->assertRedirect(route('purchase-requests.show', $pr));
    $response->assertSessionHas('success');

    $pr->refresh();

    // Should be IN_REVIEW now
    expect($pr->workflow_status)->toBe('IN_REVIEW');
    // And should have a new approval request (or reset one)
    expect($pr->approvalRequest->status)->toBe('IN_REVIEW');
});

test('non-creator cannot resubmit rejected pr', function () {
    $otherUser = User::factory()->create();
    $pr = PurchaseRequest::factory()
        ->rejected()
        ->create([
            'user_id_create' => $this->user->id,
        ]);

    $this->actingAs($otherUser);

    $response = $this->post(route('purchase-requests.sign-and-submit', $pr));

    // Should be forbidden
    expect($response->status())->toBe(403);
});

test('creator can edit rejected pr before resubmitting', function () {
    $pr = PurchaseRequest::factory()
        ->rejected()
        ->create([
            'user_id_create' => $this->user->id,
            'remark' => 'Old Remark',
        ]);

    $this->actingAs($this->user);

    // Updates remark and resubmits via the update endpoint (simulating "Sign & Submit" from Edit Form)
    $response = $this->put(route('purchase-requests.update', $pr), [
        'remark' => 'Fixed Remark',
        'submit_action' => 'sign_and_submit', // Action from Edit Form
        // Required fields for update validation
        'from_department' => 'Computer',
        'to_department' => 'Purchasing',
        'branch' => 'JAKARTA',
        'date_of_pr' => now()->format('Y-m-d'),
        'date_of_required' => now()->addDays(7)->format('Y-m-d'),
        'supplier' => 'Test Supplier',
        'pic' => 'Test PIC',
        'items' => [
            [
                'item_name' => 'Item 1',
                'quantity' => 10,
                'uom' => 'PCS',
                'price' => 100,
                'currency' => 'IDR',
                'purpose' => 'Test',
            ],
        ],
    ]);

    $response->assertRedirect(); // Redirects to show page usually

    $pr->refresh();
    expect($pr->remark)->toBe('Fixed Remark');
    expect($pr->workflow_status)->toBe('IN_REVIEW');
});

test('creator can resubmit returned pr', function () {
    $pr = PurchaseRequest::factory()
        ->create([
            'user_id_create' => $this->user->id,
            'workflow_status' => 'RETURNED',
            'status' => 3, // Assuming 3 is returned/revision
            // Required fields for context builder
            'from_department' => 'Computer',
            'to_department' => 'Purchasing',
            'branch' => 'JAKARTA',
            'type' => 'office',
        ]);

    // Create items
    \App\Models\DetailPurchaseRequest::factory()->create([
        'purchase_request_id' => $pr->id,
        'quantity' => 10,
        'price' => 1000,
    ]);

    $this->actingAs($this->user);

    $response = $this->post(route('purchase-requests.sign-and-submit', $pr));

    $response->assertRedirect(route('purchase-requests.show', $pr));
    $pr->refresh();
    expect($pr->workflow_status)->toBe('IN_REVIEW');
});
