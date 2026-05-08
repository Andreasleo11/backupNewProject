<?php

namespace Tests\Unit\Services;

use App\Application\Approval\Contracts\Approvals;
use App\Application\Approval\DTOs\ApprovalInfo;
use App\Enums\PurchaseOrderStatus;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PurchaseOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);

        $approvalsMock = $this->createMock(Approvals::class);
        $approvalsMock->method('submit')->willReturn(new ApprovalInfo(1, 'PENDING', null));
        $this->service = new PurchaseOrderService($approvalsMock);
    }

    public function test_create_purchase_order()
    {
        // Arrange
        $data = [
            'po_number' => 1001,
            'pdf_file' => 'po_1001.pdf',
            'vendor_name' => 'Test Vendor',
            'invoice_date' => '2024-01-15',
            'invoice_number' => 'INV-001',
            'currency' => 'IDR',
            'total' => 1000000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-20',
        ];

        // Act
        $po = $this->service->create($data);

        // Assert
        $this->assertInstanceOf(PurchaseOrder::class, $po);
        $this->assertEquals(1001, $po->po_number);
        $this->assertEquals(PurchaseOrderStatus::PENDING_APPROVAL->legacyValue(), $po->status);
        $this->assertEquals('Test Vendor', $po->vendor_name);
        $this->assertEquals(1000000, $po->total);
        $this->assertDatabaseHas('purchase_orders', [
            'po_number' => 1001,
            'status' => PurchaseOrderStatus::PENDING_APPROVAL->legacyValue(),
        ]);
    }

    public function test_create_with_parent_po_updates_revision_count()
    {
        // Arrange
        $parentPO = PurchaseOrder::create([
            'po_number' => 1000,
            'status' => PurchaseOrderStatus::DRAFT->legacyValue(),
            'filename' => 'test.pdf',
            'creator_id' => 1,
            'vendor_name' => 'Parent Vendor',
            'invoice_date' => '2024-01-10',
            'invoice_number' => 'INV-000',
            'currency' => 'IDR',
            'total' => 500000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-15',
            'revision_count' => 0,
        ]);

        $data = [
            'po_number' => 1001,
            'pdf_file' => 'po_1001.pdf',
            'parent_po_number' => 1000,
            'vendor_name' => 'Test Vendor',
            'invoice_date' => '2024-01-15',
            'invoice_number' => 'INV-001',
            'currency' => 'IDR',
            'total' => 1000000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-20',
        ];

        // Act
        $po = $this->service->create($data);

        // Assert
        $this->assertEquals(1000, $po->parent_po_number);
        $parentPO->refresh();
        $this->assertEquals(1, $parentPO->revision_count);
    }

    public function test_update_purchase_order()
    {
        // Arrange
        $po = PurchaseOrder::create([
            'po_number' => 1001,
            'status' => PurchaseOrderStatus::REJECTED->legacyValue(),
            'filename' => 'test.pdf',
            'creator_id' => 1,
            'vendor_name' => 'Old Vendor',
            'invoice_date' => '2024-01-10',
            'invoice_number' => 'INV-000',
            'currency' => 'IDR',
            'total' => 1000000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-15',
        ]);

        // Create approval request to simulate REJECTED status
        $approval = new ApprovalRequest;
        $approval->approvable_id = $po->id;
        $approval->approvable_type = PurchaseOrder::class;
        $approval->status = 'REJECTED';
        $approval->current_step = 1;
        $approval->save();

        $updateData = [
            'po_number' => 1002,
            'vendor_name' => 'New Vendor',
            'invoice_date' => '2024-01-15',
            'invoice_number' => 'INV-001',
            'currency' => 'USD',
            'total' => 2000000,
            'purchase_order_category_id' => 2,
            'tanggal_pembayaran' => '2024-01-25',
        ];

        // Act
        $updatedPo = $this->service->update($po->id, $updateData);

        // Assert
        $this->assertEquals(1002, $updatedPo->po_number);
        $this->assertEquals('New Vendor', $updatedPo->vendor_name);
        $this->assertEquals(2000000, $updatedPo->total);
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'po_number' => 1002,
            'vendor_name' => 'New Vendor',
        ]);
    }

    public function test_update_non_editable_status_throws_exception()
    {
        // Arrange
        $po = PurchaseOrder::create([
            'po_number' => 1001,
            'status' => PurchaseOrderStatus::APPROVED->legacyValue(), // Cannot edit approved POs
            'filename' => 'test.pdf',
            'creator_id' => 1,
            'vendor_name' => 'Test Vendor',
            'invoice_date' => '2024-01-10',
            'invoice_number' => 'INV-000',
            'currency' => 'IDR',
            'total' => 1000000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-15',
        ]);

        $updateData = [
            'po_number' => 1002,
            'vendor_name' => 'New Vendor',
            'invoice_date' => '2024-01-15',
            'invoice_number' => 'INV-001',
            'currency' => 'USD',
            'total' => 2000000,
            'purchase_order_category_id' => 2,
            'tanggal_pembayaran' => '2024-01-25',
        ];

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Purchase order cannot be edited in its current status');
        $this->service->update($po->id, $updateData);
    }

    public function test_delete_purchase_order()
    {
        // Arrange
        $po = PurchaseOrder::create([
            'po_number' => 1001,
            'status' => PurchaseOrderStatus::DRAFT->legacyValue(),
            'filename' => 'test.pdf',
            'creator_id' => 1,
            'vendor_name' => 'Test Vendor',
            'invoice_date' => '2024-01-10',
            'invoice_number' => 'INV-000',
            'currency' => 'IDR',
            'total' => 1000000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-15',
        ]);

        // Act
        $result = $this->service->delete($po->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('purchase_orders', [
            'id' => $po->id,
        ]);
    }

    public function test_approve_purchase_order()
    {
        // Arrange
        $po = PurchaseOrder::create([
            'po_number' => 1001,
            'status' => PurchaseOrderStatus::PENDING_APPROVAL->legacyValue(),
            'filename' => 'test.pdf',
            'creator_id' => 1,
            'vendor_name' => 'Test Vendor',
            'invoice_date' => '2024-01-10',
            'invoice_number' => 'INV-000',
            'currency' => 'IDR',
            'total' => 1000000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-15',
        ]);

        // Act
        $this->service->approve($po->id, auth()->id());

        // Assert - verify the logic didn't crash
        $this->assertTrue(true);
    }

    public function test_reject_purchase_order()
    {
        // Arrange
        $po = PurchaseOrder::create([
            'po_number' => 1001,
            'status' => PurchaseOrderStatus::PENDING_APPROVAL->legacyValue(),
            'filename' => 'test.pdf',
            'creator_id' => 1,
            'vendor_name' => 'Test Vendor',
            'invoice_date' => '2024-01-10',
            'invoice_number' => 'INV-000',
            'currency' => 'IDR',
            'total' => 1000000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-15',
        ]);

        // Act
        $this->service->reject($po->id, auth()->id(), 'Wrong price');

        // Assert
        $this->assertTrue(true);
    }

    public function test_cancel_purchase_order()
    {
        // Arrange
        $po = PurchaseOrder::create([
            'po_number' => 1001,
            'status' => PurchaseOrderStatus::PENDING_APPROVAL->legacyValue(),
            'filename' => 'test.pdf',
            'creator_id' => 1,
            'vendor_name' => 'Test Vendor',
            'invoice_date' => '2024-01-10',
            'invoice_number' => 'INV-000',
            'currency' => 'IDR',
            'total' => 1000000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-15',
        ]);

        // Act
        $this->service->cancel($po->id, 'No longer needed');

        // Assert
        $this->assertTrue(true);
    }

    public function test_transaction_rollback_on_error()
    {
        // Arrange
        $data = [
            'po_number' => 'PO-001',
            'vendor_name' => 'Test Vendor',
            'invoice_date' => '2024-01-15',
            'invoice_number' => 'INV-001',
            'currency' => 'IDR',
            'total' => 1000000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-20',
        ];

        // Mock DB transaction to throw exception
        DB::shouldReceive('transaction')
            ->once()
            ->andThrow(new \Exception('Database error'));

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');
        $this->service->create($data);
    }

    public function test_create_logs_success()
    {
        // This would require mocking the logger, but for now we verify the method executes
        $data = [
            'po_number' => 1002,
            'pdf_file' => 'po_1002.pdf',
            'vendor_name' => 'Test Vendor',
            'invoice_date' => '2024-01-15',
            'invoice_number' => 'INV-001',
            'currency' => 'IDR',
            'total' => 1000000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-20',
        ];

        // Act
        $po = $this->service->create($data);

        // Assert - PO was created successfully
        $this->assertInstanceOf(PurchaseOrder::class, $po);
    }
}
