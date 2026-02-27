<?php

use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Facades\Auth;

uses(DatabaseTruncation::class);

beforeEach(function () {
    $dept = Department::factory()->create(['name' => 'Computer']);

    $this->user = User::factory()->create([
        'department_id' => $dept->id,
    ]);

    $this->actingAs($this->user);

    $this->draftPr = PurchaseRequest::factory()->create([
        'user_id_create' => $this->user->id,
        'from_department' => 'Computer',
        'to_department' => 'Purchasing',
        'status' => 8, // Draft
    ]);
});

test('it can delete draft purchase request', function () {
    $response = $this->delete(route('purchase-requests.destroy', $this->draftPr->id));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('purchase_requests', [
        'id' => $this->draftPr->id,
    ]);
});

test('it cannot delete non draft purchase request', function () {
    $submittedPr = PurchaseRequest::factory()->create([
        'user_id_create' => $this->user->id,
        'status' => 1, // Submitted, not draft
    ]);

    $response = $this->delete(route('purchase-requests.destroy', $submittedPr->id));

    $response->assertForbidden();

    $this->assertDatabaseHas('purchase_requests', [
        'id' => $submittedPr->id,
        'deleted_at' => null,
    ]);
});

test('it cannot delete approved purchase request', function () {
    // ✅ Use approved() factory state
    $approvedPr = PurchaseRequest::factory()
        ->approved()
        ->create([
            'user_id_create' => $this->user->id,
        ]);

    $response = $this->delete(route('purchase-requests.destroy', $approvedPr->id));

    $response->assertForbidden();
});

test('user can only delete their own purchase requests', function () {
    $otherUser = User::factory()->create();
    $otherDraftPr = PurchaseRequest::factory()->create([
        'user_id_create' => $otherUser->id,
        'status' => 8, // Draft
    ]);

    $response = $this->delete(route('purchase-requests.destroy', $otherDraftPr->id));

    $response->assertForbidden();

    $this->assertDatabaseHas('purchase_requests', [
        'id' => $otherDraftPr->id,
        'deleted_at' => null,
    ]);
});

test('deletion requires authentication', function () {
    Auth::logout();

    $response = $this->delete(route('purchase-requests.destroy', $this->draftPr->id));

    $response->assertRedirect(route('login'));

    $this->actingAs($this->user);
});

test('deleting purchase request also soft deletes items', function () {
    $this->draftPr->items()->create([
        'item_name' => 'Test Item',
        'quantity' => 10,
        'uom' => 'PCS',
        'price' => 100,
        'currency' => 'IDR',
        'purpose' => 'Testing',
    ]);

    $response = $this->delete(route('purchase-requests.destroy', $this->draftPr->id));

    $this->assertSoftDeleted('purchase_requests', [
        'id' => $this->draftPr->id,
    ]);

    $this->assertSoftDeleted('detail_purchase_requests', [
        'purchase_request_id' => $this->draftPr->id,
    ]);
});
