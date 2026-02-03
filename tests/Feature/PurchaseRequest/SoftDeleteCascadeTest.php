<?php

use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use App\Models\DetailPurchaseRequest;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('soft deleting purchase request cascades to items', function () {
    // Arrange: Create a user, PR, and items
    $user = User::factory()->create();

    $pr = PurchaseRequest::factory()->create([
        'user_id_create' => $user->id,
    ]);

    $item1 = DetailPurchaseRequest::create([
        'purchase_request_id' => $pr->id,
        'item_name' => 'Test Item 1',
        'quantity' => 10,
        'purpose' => 'Testing',
    ]);

    $item2 = DetailPurchaseRequest::create([
        'purchase_request_id' => $pr->id,
        'item_name' => 'Test Item 2',
        'quantity' => 5,
        'purpose' => 'Testing',
    ]);

    // Act: Soft delete the purchase request via repository (DDD pattern)
    $repo = app(PurchaseRequestRepository::class);
    $repo->delete($pr);

    // Assert: Purchase request is soft deleted
    expect($pr->fresh()->trashed())->toBeTrue();

    // Assert: Items are also soft deleted
    expect($item1->fresh()->trashed())->toBeTrue();
    expect($item2->fresh()->trashed())->toBeTrue();

    // Assert: Items still exist in database (soft deleted, not hard deleted)
    $this->assertDatabaseHas('detail_purchase_requests', [
        'id' => $item1->id,
    ]);
    $this->assertDatabaseHas('detail_purchase_requests', [
        'id' => $item2->id,
    ]);
});

test('restoring purchase request restores items', function () {
    // Arrange: Create PR and items, then soft delete them
    $repo = app(PurchaseRequestRepository::class);
    $user = User::factory()->create();

    $pr = PurchaseRequest::factory()->create([
        'user_id_create' => $user->id,
    ]);

    $item = DetailPurchaseRequest::create([
        'purchase_request_id' => $pr->id,
        'item_name' => 'Test Item',
        'quantity' => 10,
        'purpose' => 'Testing',
    ]);

    $repo->delete($pr); // Soft delete via repository (cascades to items)

    expect($pr->fresh()->trashed())->toBeTrue();
    expect($item->fresh()->trashed())->toBeTrue();

    // Act: Restore the purchase request via repository
    $repo->restore($pr);

    // Assert: Purchase request is restored
    expect($pr->fresh()->trashed())->toBeFalse();

    // Assert: Item is also restored
    expect($item->fresh()->trashed())->toBeFalse();
});

test('force deleting purchase request permanently deletes items', function () {
    // Arrange: Create PR and items
    $repo = app(PurchaseRequestRepository::class);
    $user = User::factory()->create();

    $pr = PurchaseRequest::factory()->create([
        'user_id_create' => $user->id,
    ]);

    $item = DetailPurchaseRequest::create([
        'purchase_request_id' => $pr->id,
        'item_name' => 'Test Item',
        'quantity' => 10,
        'purpose' => 'Testing',
    ]);

    $itemId = $item->id;
    $prId = $pr->id;

    // Act: Force delete the purchase request via repository
    $repo->forceDelete($pr);

    // Assert: Purchase request is permanently deleted
    $this->assertDatabaseMissing('purchase_requests', [
        'id' => $prId,
    ]);

    // Assert: Item is also permanently deleted
    $this->assertDatabaseMissing('detail_purchase_requests', [
        'id' => $itemId,
    ]);
});

test('soft deleting PR with already soft deleted items', function () {
    // Arrange: Create PR and items, soft delete one item manually
    $repo = app(PurchaseRequestRepository::class);
    $user = User::factory()->create();

    $pr = PurchaseRequest::factory()->create([
        'user_id_create' => $user->id,
    ]);

    $item1 = DetailPurchaseRequest::create([
        'purchase_request_id' => $pr->id,
        'item_name' => 'Item 1',
        'quantity' => 10,
        'purpose' => 'Testing',
    ]);

    $item2 = DetailPurchaseRequest::create([
        'purchase_request_id' => $pr->id,
        'item_name' => 'Item 2',
        'quantity' => 5,
        'purpose' => 'Testing',
    ]);

    // Manually soft delete item1 first
    $item1->delete();
    expect($item1->trashed())->toBeTrue();

    // Act: Soft delete the PR via repository
    $repo->delete($pr);

    // Assert: Both items are soft deleted
    expect($item1->fresh()->trashed())->toBeTrue();
    expect($item2->fresh()->trashed())->toBeTrue();

    // Act: Restore PR via repository
    $repo->restore($pr);

    // Assert: Both items are restored (including the one that was already deleted)
    expect($item1->fresh()->trashed())->toBeFalse();
    expect($item2->fresh()->trashed())->toBeFalse();
});
