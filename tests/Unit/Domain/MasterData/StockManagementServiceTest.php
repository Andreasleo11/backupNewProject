<?php

namespace Tests\Unit\Domain\MasterData;

use App\Domain\MasterData\Services\StockManagementService;
use App\Models\Department;
use App\Models\MasterStock;
use App\Models\StockRequest;
use App\Models\StockTransaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockManagementService();
    }

    /** @test */
    public function it_can_process_out_transaction()
    {
        $stock = MasterStock::factory()->create(['stock_quantity' => 10]);
        $department = Department::factory()->create();
        $transaction = StockTransaction::factory()->create([
            'stock_id' => $stock->id,
            'unique_code' => 'ITEM-001',
            'is_out' => false
        ]);

        StockRequest::factory()->create([
            'stock_id' => $stock->id,
            'dept_id' => $department->id,
            'quantity_available' => 5,
            'created_at' => now()
        ]);

        $data = [
            'stock_id' => $stock->id,
            'transaction_type' => 'out',
            'item_name_1' => 'ITEM-001',
            'department' => $department->id,
            'pic' => 'John Doe',
            'remark' => 'Test remark'
        ];

        $this->service->storeTransaction($data);

        $transaction->refresh();
        $stock->refresh();

        $this->assertTrue($transaction->is_out);
        $this->assertEquals($department->id, $transaction->dept_id);
        $this->assertEquals('John Doe', $transaction->receiver);
        $this->assertEquals(9, $stock->stock_quantity);
    }

    /** @test */
    public function it_can_process_in_transaction()
    {
        $stock = MasterStock::factory()->create(['stock_quantity' => 10]);

        $data = [
            'stock_id' => $stock->id,
            'transaction_type' => 'in',
            'item_name_1' => 'ITEM-001',
            'item_name_2' => 'ITEM-002',
            'item_name_3' => 'ITEM-003'
        ];

        $this->service->storeTransaction($data);

        $stock->refresh();

        $this->assertEquals(13, $stock->stock_quantity);
        $this->assertDatabaseCount('stock_transactions', 3);
        $this->assertDatabaseHas('stock_transactions', [
            'stock_id' => $stock->id,
            'unique_code' => 'ITEM-001'
        ]);
    }

    /** @test */
    public function it_can_get_available_items_for_stock()
    {
        $stock = MasterStock::factory()->create();
        StockTransaction::factory()->count(3)->create([
            'stock_id' => $stock->id,
            'is_out' => false
        ]);
        StockTransaction::factory()->count(2)->create([
            'stock_id' => $stock->id,
            'is_out' => true
        ]);

        $items = $this->service->getAvailableItems($stock->id);

        $this->assertCount(3, $items);
    }

    /** @test */
    public function it_can_create_stock_request_with_availability_calculation()
    {
        $stock = MasterStock::factory()->create(['stock_quantity' => 100]);
        $department = Department::factory()->create();

        $data = [
            'masterStock' => $stock->id,
            'department' => $department->id,
            'stockRequest' => 20,
            'month' => '2026-01-01',
            'remark' => 'Test request'
        ];

        $request = $this->service->createStockRequest($data);

        $this->assertDatabaseHas('stock_requests', [
            'stock_id' => $stock->id,
            'dept_id' => $department->id,
            'request_quantity' => 20,
            'quantity_available' => 20
        ]);
    }

    /** @test */
    public function it_calculates_available_quantity_correctly_when_stock_is_limited()
    {
        $stock = MasterStock::factory()->create(['stock_quantity' => 15]);
        $department = Department::factory()->create();

        StockRequest::factory()->create([
            'stock_id' => $stock->id,
            'dept_id' => Department::factory()->create()->id,
            'quantity_available' => 10,
            'month' => now()
        ]);

        $data = [
            'masterStock' => $stock->id,
            'department' => $department->id,
            'stockRequest' => 20,
            'month' => now()->format('Y-m-d'),
            'remark' => 'Test'
        ];

        $request = $this->service->createStockRequest($data);

        // Available: 15 - 10 = 5
        $this->assertEquals(5, $request->quantity_available);
    }

    /** @test */
    public function it_can_get_available_quantity_for_department()
    {
        $stock = MasterStock::factory()->create();
        $department = Department::factory()->create();

        StockRequest::factory()->create([
            'stock_id' => $stock->id,
            'dept_id' => $department->id,
            'quantity_available' => 25,
            'month' => now()
        ]);

        $quantity = $this->service->getAvailableQuantity($stock->id, $department->id);

        $this->assertEquals(25, $quantity);
    }

    /** @test */
    public function it_returns_zero_when_no_stock_request_exists()
    {
        $quantity = $this->service->getAvailableQuantity(999, 999);

        $this->assertEquals(0, $quantity);
    }

    /** @test */
    public function it_can_filter_stock_requests_by_multiple_criteria()
    {
        $stock1 = MasterStock::factory()->create();
        $stock2 = MasterStock::factory()->create();
        $dept = Department::factory()->create();

        StockRequest::factory()->create([
            'stock_id' => $stock1->id,
            'dept_id' => $dept->id,
            'month' => '2026-01-15'
        ]);
        StockRequest::factory()->create([
            'stock_id' => $stock2->id,
            'dept_id' => $dept->id,
            'month' => '2026-02-15'
        ]);

        $filters = [
            'stock_id' => $stock1->id,
            'dept_id' => $dept->id,
            'month' => '2026-01-15'
        ];

        $results = $this->service->getFilteredStockRequests($filters);

        $this->assertCount(1, $results);
    }

    /** @test */
    public function it_can_get_filtered_transactions_by_stock_id()
    {
        $stock1 = MasterStock::factory()->create();
        $stock2 = MasterStock::factory()->create();

        StockTransaction::factory()->count(3)->create(['stock_id' => $stock1->id]);
        StockTransaction::factory()->count(2)->create(['stock_id' => $stock2->id]);

        $results = $this->service->getFilteredTransactions($stock1->id);

        $this->assertCount(3, $results);
    }
}
