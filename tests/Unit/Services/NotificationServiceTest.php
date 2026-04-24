<?php

namespace Tests\Unit\Services;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\Notifications\PurchaseOrderApproved;
use App\Notifications\PurchaseOrderCreated;
use App\Notifications\PurchaseOrderRejected;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new NotificationService;
        Notification::fake();
    }

    public function test_send_purchase_order_created()
    {
        // Arrange
        $director = User::create([
            'name' => 'Test Director',
            'email' => 'director@test.com',
            'password' => bcrypt('password'),
        ]);
        $director->assignRole('DIRECTOR');

        $po = PurchaseOrder::create([
            'po_number' => 1001,
            'status' => 1,
            'filename' => 'test.pdf',
            'creator_id' => 1,
            'vendor_name' => 'Test Vendor',
            'invoice_date' => '2024-01-15',
            'invoice_number' => 'INV-001',
            'currency' => 'IDR',
            'total' => 1000000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-20',
        ]);

        // Act
        $this->service->sendPurchaseOrderCreated($po);

        // Assert
        Notification::assertSentTo(
            [$director],
            PurchaseOrderCreated::class
        );
    }

    public function test_send_purchase_order_approved()
    {
        // Arrange
        $creator = User::create([
            'name' => 'Creator',
            'email' => 'creator@test.com',
            'password' => bcrypt('password'),
        ]);
        $deptHead = User::create([
            'name' => 'benny',
            'email' => 'benny@test.com',
            'password' => bcrypt('password'),
        ]);
        $accountingUser = User::create([
            'name' => 'nessa',
            'email' => 'nessa@test.com',
            'password' => bcrypt('password'),
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 1002,
            'status' => 2,
            'filename' => 'test.pdf',
            'creator_id' => $creator->id,
            'vendor_name' => 'Test Vendor',
            'invoice_date' => '2024-01-15',
            'invoice_number' => 'INV-001',
            'currency' => 'IDR',
            'total' => 1000000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-20',
        ]);

        // Act
        $this->service->sendPurchaseOrderApproved($po);

        // Assert
        Notification::assertSentTo(
            [$creator, $deptHead, $accountingUser],
            PurchaseOrderApproved::class
        );
    }

    public function test_send_purchase_order_rejected()
    {
        // Arrange
        $creator = User::create([
            'name' => 'Creator',
            'email' => 'creator@test.com',
            'password' => bcrypt('password'),
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 1003,
            'status' => 3,
            'filename' => 'test.pdf',
            'creator_id' => $creator->id,
            'vendor_name' => 'Test Vendor',
            'invoice_date' => '2024-01-15',
            'invoice_number' => 'INV-001',
            'currency' => 'IDR',
            'total' => 1000000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-20',
        ]);

        // Act
        $this->service->sendPurchaseOrderRejected($po);

        // Assert
        Notification::assertSentTo(
            [$creator],
            PurchaseOrderRejected::class
        );
    }

    public function test_prepare_notification_details()
    {
        // Arrange
        $po = PurchaseOrder::create([
            'po_number' => 1001,
            'status' => 2,
            'filename' => 'test.pdf',
            'creator_id' => 1,
            'vendor_name' => 'Test Vendor',
            'invoice_date' => '2024-01-15',
            'invoice_number' => 'INV-001',
            'currency' => 'IDR',
            'total' => 1000000.50,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-20',
        ]);

        // Act
        $service = new NotificationService;
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('prepareNotificationDetails');
        $method->setAccessible(true);
        $details = $method->invoke($service, $po);

        // Assert
        $this->assertEquals('Purchase Order Notification', $details['greeting']);
        $this->assertEquals('Check Now', $details['actionText']);
        $this->assertTrue(strpos($details['body'], 'PO Number : 1001') !== false);
        $this->assertTrue(strpos($details['body'], 'Vendor Name : Test Vendor') !== false);
        $this->assertTrue(strpos($details['body'], 'Total : IDR 1,000,000.50') !== false);
        $this->assertTrue(strpos($details['body'], 'Status : APPROVED') !== false);
    }

    public function test_get_notification_users_for_created()
    {
        // Arrange
        $director = User::create([
            'name' => 'Director',
            'email' => 'director@test.com',
            'password' => bcrypt('password'),
        ]);
        $director->assignRole('DIRECTOR');

        $po = PurchaseOrder::create([
            'po_number' => 1001,
            'status' => 1,
            'filename' => 'test.pdf',
            'creator_id' => 1,
            'vendor_name' => 'Test Vendor',
            'invoice_date' => '2024-01-15',
            'invoice_number' => 'INV-001',
            'currency' => 'IDR',
            'total' => 1000000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-20',
        ]);

        // Act
        $service = new NotificationService;
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getNotificationUsers');
        $method->setAccessible(true);
        $users = $method->invoke($service, $po, 'created');

        // Assert
        $this->assertCount(1, $users);
        $this->assertEquals($director->id, $users->first()->id);
    }

    public function test_get_notification_users_for_approved()
    {
        // Arrange
        $creator = User::create([
            'name' => 'Creator',
            'email' => 'creator@test.com',
            'password' => bcrypt('password'),
        ]);
        $deptHead = User::create([
            'name' => 'benny',
            'email' => 'benny@test.com',
            'password' => bcrypt('password'),
        ]);
        $accountingUser = User::create([
            'name' => 'nessa',
            'email' => 'nessa@test.com',
            'password' => bcrypt('password'),
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 1001,
            'status' => 1,
            'filename' => 'test.pdf',
            'creator_id' => $creator->id,
            'vendor_name' => 'Test Vendor',
            'invoice_date' => '2024-01-15',
            'invoice_number' => 'INV-001',
            'currency' => 'IDR',
            'total' => 1000000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-20',
        ]);

        // Act
        $service = new NotificationService;
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('getNotificationUsers');
        $method->setAccessible(true);
        $users = $method->invoke($service, $po, 'approved');

        // Assert
        $this->assertCount(3, $users);
        $userIds = $users->pluck('id')->toArray();
        $this->assertContains($creator->id, $userIds);
        $this->assertContains($deptHead->id, $userIds);
        $this->assertContains($accountingUser->id, $userIds);
    }

    public function test_send_custom_notification()
    {
        // Arrange
        $user1 = User::create([
            'name' => 'User1',
            'email' => 'user1@test.com',
            'password' => bcrypt('password'),
        ]);
        $user2 = User::create([
            'name' => 'User2',
            'email' => 'user2@test.com',
            'password' => bcrypt('password'),
        ]);

        // Act
        $this->service->sendCustomNotification(
            [$user1->id, $user2->id],
            'Test Subject',
            'Test Message',
            'https://example.com'
        );

        // Assert
        Notification::assertSentTo(
            [$user1, $user2],
            \App\Notifications\CustomNotification::class
        );
    }

    public function test_notification_handles_empty_recipients()
    {
        // Arrange - Remove all directors
        User::role('DIRECTOR')->delete();

        $po = PurchaseOrder::create([
            'po_number' => 1001,
            'status' => 1,
            'filename' => 'test.pdf',
            'creator_id' => 1,
            'vendor_name' => 'Test Vendor',
            'invoice_date' => '2024-01-15',
            'invoice_number' => 'INV-001',
            'currency' => 'IDR',
            'total' => 1000000,
            'purchase_order_category_id' => 1,
            'tanggal_pembayaran' => '2024-01-20',
        ]);

        // Act - Should not throw exception
        $this->service->sendPurchaseOrderCreated($po);

        // Assert - No notifications sent
        Notification::assertNothingSent();
    }
}
