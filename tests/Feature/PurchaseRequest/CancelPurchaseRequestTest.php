<?php

use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;

uses(DatabaseTruncation::class);

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
    $response = $this->put(route('purchase-requests.cancel', $this->pr->id), [
        'description' => 'Test reason',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('purchase_requests', [
        'id' => $this->pr->id,
        'status' => 8, // CANCELED
    ]);
});

test('it cannot cancel approved purchase request', function () {
    // ✅ Use approved() factory state
    $approvedPr = PurchaseRequest::factory()
        ->approved()
        ->create([
            'user_id_create' => $this->user->id,
        ]);

    $this->actingAs($this->user);

    $response = $this->put(route('purchase-requests.cancel', $approvedPr->id), [
        'description' => 'Attempt to cancel approved',
    ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('purchase_requests', [
        'id' => $approvedPr->id,
        'status' => 4, // Still approved
    ]);
});

test('it cannot cancel already cancelled purchase request', function () {
    // ✅ Use cancelled() factory state
    $cancelledPr = PurchaseRequest::factory()
        ->cancelled()
        ->create([
            'user_id_create' => $this->user->id,
        ]);

    $this->actingAs($this->user);

    $response = $this->put(route('purchase-requests.cancel', $cancelledPr->id), [
        'description' => 'Attempt to cancel already cancelled',
    ]);

    $response->assertForbidden();
});

test('user can only cancel their own purchase requests', function () {
    $otherUser = User::factory()->create();
    $otherPr = PurchaseRequest::factory()->create([
        'user_id_create' => $otherUser->id,
        'status' => 1,
    ]);

    $this->actingAs($this->user);

    $response = $this->put(route('purchase-requests.cancel', $otherPr->id), [
        'description' => 'Attempt to cancel other user pr',
    ]);

    $response->assertForbidden();

    $this->assertDatabaseHas('purchase_requests', [
        'id' => $otherPr->id,
        'status' => 1, // Status unchanged
    ]);
});

test('cancellation requires authentication', function () {
    $response = $this->put(route('purchase-requests.cancel', $this->pr->id));

    $response->assertRedirect(route('login'));
});
