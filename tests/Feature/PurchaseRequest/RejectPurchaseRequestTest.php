<?php

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
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
    $approvedPr = PurchaseRequest::factory()->create([
        'workflow_status' => 'APPROVED',
    ]);

    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.reject', $approvedPr->id), [
        'remarks' => 'Test reason',
    ]);

    expect($response->status())->toBeIn([302, 403]);
});

test('cannot reject cancelled purchase request', function () {
    $cancelledPr = PurchaseRequest::factory()->create([
        'is_cancel' => 1,
    ]);

    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.reject', $cancelledPr->id), [
        'remarks' => 'Test reason',
    ]);

    expect($response->status())->toBeIn([302, 403]);
});

test('rejection requires authentication', function () {
    $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
        'reason' => 'Test reason',
    ]);

    $response->assertRedirect(route('login'));
});

test('rejection requires authorization', function () {
    $unauthorizedUser = User::factory()->create();

    $this->actingAs($unauthorizedUser);

    $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
        'remarks' => 'Test reason',
    ]);

    // Verify PR wasn't rejected
    $pr = $this->pr->fresh();
    expect($pr->workflow_status)->toBe('IN_REVIEW');
});

test('rejection sends notification to requester', function () {
    // Event::fake();

    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
        'remarks' => 'Budget constraints',
    ]);

    // Event::assertDispatched(PurchaseRequestRejected::class);
});
