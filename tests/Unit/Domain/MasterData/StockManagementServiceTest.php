<?php

use App\Domain\MasterData\Services\StockManagementService;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Models\MasterStock;
use App\Models\StockRequest;
use App\Models\StockTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new StockManagementService;
});

test('it can process out transaction', function () {
    $stock = MasterStock::factory()->create(['stock_quantity' => 10]);
    $department = Department::factory()->create();
    $transaction = StockTransaction::factory()->create([
        'stock_id' => $stock->id,
        'unique_code' => 'ITEM-001',
        'is_out' => false,
    ]);

    StockRequest::factory()->create([
        'stock_id' => $stock->id,
        'dept_id' => $department->id,
        'quantity_available' => 5,
        'created_at' => now(),
    ]);

    $data = [
        'stock_id' => $stock->id,
        'transaction_type' => 'out',
        'item_name_1' => 'ITEM-001',
        'department' => $department->id,
        'pic' => 'John Doe',
        'remark' => 'Test remark',
    ];

    $this->service->storeTransaction($data);

    $transaction->refresh();
    $stock->refresh();

    expect($transaction->is_out)->toBe(1);
    expect($transaction->dept_id)->toBe($department->id);
    expect($transaction->receiver)->toBe('John Doe');
    expect($stock->stock_quantity)->toBe(9);
});

test('it can process in transaction', function () {
    $stock = MasterStock::factory()->create(['stock_quantity' => 10]);

    $data = [
        'stock_id' => $stock->id,
        'transaction_type' => 'in',
        'item_name_1' => 'ITEM-001',
        'item_name_2' => 'ITEM-002',
        'item_name_3' => 'ITEM-003',
    ];

    $this->service->storeTransaction($data);

    $stock->refresh();

    expect($stock->stock_quantity)->toBe(13);
    $this->assertDatabaseCount('stock_transactions', 3);
    $this->assertDatabaseHas('stock_transactions', [
        'stock_id' => $stock->id,
        'unique_code' => 'ITEM-001',
    ]);
});

test('it can get available items for stock', function () {
    $stock = MasterStock::factory()->create();
    StockTransaction::factory()->count(3)->create([
        'stock_id' => $stock->id,
        'is_out' => false,
    ]);
    StockTransaction::factory()->count(2)->create([
        'stock_id' => $stock->id,
        'is_out' => true,
    ]);

    $items = $this->service->getAvailableItems($stock->id);

    expect($items)->toHaveCount(3);
});

test('it can create stock request with availability calculation', function () {
    $stock = MasterStock::factory()->create(['stock_quantity' => 100]);
    $department = Department::factory()->create();

    $data = [
        'masterStock' => $stock->id,
        'department' => $department->id,
        'stockRequest' => 20,
        'month' => '2026-01-01',
        'remark' => 'Test request',
    ];

    $request = $this->service->createStockRequest($data);

    $this->assertDatabaseHas('stock_requests', [
        'stock_id' => $stock->id,
        'dept_id' => $department->id,
        'request_quantity' => 20,
        'quantity_available' => 20,
    ]);
});

test('it calculates available quantity correctly when stock is limited', function () {
    $stock = MasterStock::factory()->create(['stock_quantity' => 15]);
    $department = Department::factory()->create();

    StockRequest::factory()->create([
        'stock_id' => $stock->id,
        'dept_id' => Department::factory()->create()->id,
        'quantity_available' => 10,
        'month' => now(),
    ]);

    $data = [
        'masterStock' => $stock->id,
        'department' => $department->id,
        'stockRequest' => 20,
        'month' => now()->format('Y-m-d'),
        'remark' => 'Test',
    ];

    $request = $this->service->createStockRequest($data);

    // Available: 15 - 10 = 5
    expect($request->quantity_available)->toBe(5);
});

test('it can get available quantity for department', function () {
    $stock = MasterStock::factory()->create();
    $department = Department::factory()->create();

    StockRequest::factory()->create([
        'stock_id' => $stock->id,
        'dept_id' => $department->id,
        'quantity_available' => 25,
        'month' => now(),
    ]);

    $quantity = $this->service->getAvailableQuantity($stock->id, $department->id);

    expect($quantity)->toBe(25);
});

test('it returns zero when no stock request exists', function () {
    $quantity = $this->service->getAvailableQuantity(999, 999);

    expect($quantity)->toBe(0);
});

test('it can filter stock requests by multiple criteria', function () {
    $stock1 = MasterStock::factory()->create();
    $stock2 = MasterStock::factory()->create();
    $dept = Department::factory()->create();

    StockRequest::factory()->create([
        'stock_id' => $stock1->id,
        'dept_id' => $dept->id,
        'month' => '2026-01-15',
    ]);
    StockRequest::factory()->create([
        'stock_id' => $stock2->id,
        'dept_id' => $dept->id,
        'month' => '2026-02-15',
    ]);

    $filters = [
        'stock_id' => $stock1->id,
        'dept_id' => $dept->id,
        'month' => '2026-01-15',
    ];

    $results = $this->service->getFilteredStockRequests($filters);

    expect($results)->toHaveCount(1);
});

test('it can get filtered transactions by stock id', function () {
    $stock1 = MasterStock::factory()->create();
    $stock2 = MasterStock::factory()->create();

    StockTransaction::factory()->count(3)->create(['stock_id' => $stock1->id]);
    StockTransaction::factory()->count(2)->create(['stock_id' => $stock2->id]);

    $results = $this->service->getFilteredTransactions($stock1->id);

    expect($results)->toHaveCount(3);
});
