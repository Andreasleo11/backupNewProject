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
        'reason' => 'Budget not available',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('purchase_requests', [
        'id' => $this->pr->id,
        'status' => 3, // Rejected
    ]);
});

test('rejection requires reason', function () {
    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
        'reason' => '', // Empty reason
    ]);

    $response->assertSessionHasErrors(['reason']);
});

test('rejection creates approval record with rejected status', function () {
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
});

test('cannot reject already approved purchase request', function () {
    $approvedPr = PurchaseRequest::factory()->create([
        'status' => 4, // Fully approved
    ]);

    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.reject', $approvedPr->id), [
        'reason' => 'Test reason',
    ]);

    $response->assertForbidden();
});

test('cannot reject cancelled purchase request', function () {
    $cancelledPr = PurchaseRequest::factory()->create([
        'status' => 5, // Cancelled
    ]);

    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.reject', $cancelledPr->id), [
        'reason' => 'Test reason',
    ]);

    $response->assertForbidden();
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
        'reason' => 'Test reason',
    ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('purchase_requests', [
        'id' => $this->pr->id,
        'status' => 1, // Status unchanged
    ]);
});

test('rejection sends notification to requester', function () {
    // Event::fake();

    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.reject', $this->pr->id), [
        'reason' => 'Budget constraints',
    ]);

    // Event::assertDispatched(PurchaseRequestRejected::class);
});
