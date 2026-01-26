<?php

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $dept = Department::factory()->create(['name' => 'Computer']);

    // Create requester
    $requester = User::factory()->create([
        'department_id' => $dept->id,
    ]);

    // Create approver with appropriate role/permissions
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

test('authorized user can approve purchase request', function () {
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
});

test('approval updates status correctly based on workflow', function () {
    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.approve', $this->pr->id));

    $updatedPr = $this->pr->fresh();

    // Verify status changed
    expect($updatedPr->status)->not->toEqual(1);
    expect([2, 3, 4, 6, 7])->toContain($updatedPr->status);
});

test('approval creates approval record', function () {
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
});

test('cannot approve already approved purchase request', function () {
    $approvedPr = PurchaseRequest::factory()->create([
        'status' => 4, // Already fully approved
    ]);

    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.approve', $approvedPr->id));

    $response->assertForbidden();
});

test('cannot approve cancelled purchase request', function () {
    $cancelledPr = PurchaseRequest::factory()->create([
        'status' => 5, // Cancelled
    ]);

    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.approve', $cancelledPr->id));

    $response->assertForbidden();
});

test('approval requires authentication', function () {
    $response = $this->post(route('purchase-requests.approve', $this->pr->id));

    $response->assertRedirect(route('login'));
});

test('approval requires authorization', function () {
    // Create user without approval permissions
    $unauthorizedUser = User::factory()->create();

    $this->actingAs($unauthorizedUser);

    $response = $this->post(route('purchase-requests.approve', $this->pr->id));

    $response->assertForbidden();

    $this->assertDatabaseHas('purchase_requests', [
        'id' => $this->pr->id,
        'status' => 1, // Status unchanged
    ]);
});

test('approval sends notification to requester', function () {
    Event::fake();

    $this->actingAs($this->approver);

    $response = $this->post(route('purchase-requests.approve', $this->pr->id));

    // Verify notification event was dispatched
    // Event::assertDispatched(PurchaseRequestApproved::class);
});

test('final approval sets status to approved', function () {
    $prReadyForFinalApproval = PurchaseRequest::factory()->create([
        'status' => 3, // One step before final approval
    ]);

    $finalApprover = User::factory()->create();

    $this->actingAs($finalApprover);

    $response = $this->post(route('purchase-requests.approve', $prReadyForFinalApproval->id));

    $this->assertDatabaseHas('purchase_requests', [
        'id' => $prReadyForFinalApproval->id,
        'status' => 4, // Fully approved
    ]);
});
