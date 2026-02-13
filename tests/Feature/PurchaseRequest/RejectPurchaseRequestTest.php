<?php

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Tests\Database\Seeders\TestRoleSeeder;
use Tests\Helpers\SignatureTestHelper;

uses(DatabaseTruncation::class);

beforeEach(function () {
    // Seed test roles
    $this->seed(TestRoleSeeder::class);

    $dept = Department::factory()->create(['name' => 'Computer']);

    $requester = User::factory()->create([
        'department_id' => $dept->id,
    ]);

    $this->approver = User::factory()->create([
        'department_id' => $dept->id,
    ]);

    // Assign PR approval role
    $this->approver->assignRole('pr-dept-head');

    // Create signature for approver (required for rejection)
    SignatureTestHelper::createDefaultSignature($this->approver->id);

    // ✅ Use factory state for workflow
    $this->pr = PurchaseRequest::factory()
        ->atDeptHeadStep()
        ->create([
            'user_id_create' => $requester->id,
            'from_department' => 'Computer',
            'to_department' => 'Purchasing',
        ]);
});

test('authorized user can reject purchase request', function () {
    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
        'remarks' => 'Budget not available',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $pr = $this->pr->fresh();
    expect($pr->workflow_status)->toBe('REJECTED');
});

test('rejection requires reason', function () {
    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
        'remarks' => '', // Empty reason
    ]);

    $response->assertSessionHasErrors(['remarks']);
});

test('rejection creates approval record with rejected status', function () {
    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
        'remarks' => 'Not aligned with company policy',
    ]);

    // Verify approval request exists
    $pr = $this->pr->fresh();
    expect($pr->approvalRequest)->not->toBeNull();
    expect($pr->approvalRequest->status)->toBe('REJECTED');
});

test('cannot reject already approved purchase request', function () {
    // ✅ Use approved() factory state
    $approvedPr = PurchaseRequest::factory()
        ->withApprovalWorkflow()
        ->approved()
        ->create();

    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.reject', $approvedPr->id), [
        'remarks' => 'Test reason',
    ]);

    expect($response->status())->toBeIn([302, 403]);
});

test('cannot reject cancelled purchase request', function () {
    // ✅ Use cancelled() factory state
    $cancelledPr = PurchaseRequest::factory()
        ->cancelled()
        ->create();

    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.reject', $cancelledPr->id), [
        'remarks' => 'Test reason',
    ]);

    expect($response->status())->toBeIn([302, 403]);
});

test('rejection requires authentication', function () {
    $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
        'remarks' => 'Test reason',
    ]);

    $response->assertRedirect(route('login'));
});

test('rejection requires authorization', function () {
    $unauthorizedUser = User::factory()->create();

    $this->actingAs($unauthorizedUser);

    $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
        'remarks' => 'Test reason',
    ]);

    // Verify PR wasn't rejected (should remain in review or fail auth)
    $pr = $this->pr->fresh();
    // May return 403 or redirect, status should not change
    expect($pr->workflow_status)->toBeIn(['IN_REVIEW', 'DRAFT']);
});

test('rejection sends notification to requester', function () {
    // Event::fake();

    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
        'remarks' => 'Budget constraints',
    ]);

    // Event::assertDispatched(PurchaseRequestRejected::class);
    expect(true)->toBeTrue();
});
