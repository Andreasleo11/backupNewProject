<?php

use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
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
});

test('it can cancel pending purchase request', function () {
    $this->actingAs($this->user);

    $response = $this->post(route('purchase-requests.cancel', $this->pr->id));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('purchase_requests', [
        'id' => $this->pr->id,
        'status' => 5, // Cancelled
    ]);
});

test('it cannot cancel approved purchase request', function () {
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
});

test('it cannot cancel already cancelled purchase request', function () {
    $cancelledPr = PurchaseRequest::factory()->create([
        'user_id_create' => $this->user->id,
        'status' => 5, // Already cancelled
    ]);

    $this->actingAs($this->user);

    $response = $this->post(route('purchase-requests.cancel', $cancelledPr->id));

    $response->assertForbidden();
});

test('user can only cancel their own purchase requests', function () {
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
});

test('cancellation requires authentication', function () {
    $response = $this->post(route('purchase-requests.cancel', $this->pr->id));

    $response->assertRedirect(route('login'));
});
