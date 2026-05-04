<?php

namespace Tests\Unit\Enums;

use App\Enums\PurchaseOrderStatus;
use PHPUnit\Framework\TestCase;

class PurchaseOrderStatusTest extends TestCase
{
    public function test_enum_values()
    {
        $this->assertEquals(1, PurchaseOrderStatus::DRAFT->value);
        $this->assertEquals(2, PurchaseOrderStatus::WAITING->value);
        $this->assertEquals(3, PurchaseOrderStatus::APPROVED->value);
        $this->assertEquals(4, PurchaseOrderStatus::REJECTED->value);
        $this->assertEquals(5, PurchaseOrderStatus::CANCELLED->value);
    }

    public function test_labels()
    {
        $this->assertEquals('Draft', PurchaseOrderStatus::DRAFT->label());
        $this->assertEquals('Waiting for Approval', PurchaseOrderStatus::WAITING->label());
        $this->assertEquals('Approved', PurchaseOrderStatus::APPROVED->label());
        $this->assertEquals('Rejected', PurchaseOrderStatus::REJECTED->label());
        $this->assertEquals('Cancelled', PurchaseOrderStatus::CANCELLED->label());
    }

    public function test_legacy_values()
    {
        $this->assertEquals(1, PurchaseOrderStatus::DRAFT->legacyValue());
        $this->assertEquals(2, PurchaseOrderStatus::WAITING->legacyValue());
        $this->assertEquals(3, PurchaseOrderStatus::APPROVED->legacyValue());
        $this->assertEquals(4, PurchaseOrderStatus::REJECTED->legacyValue());
        $this->assertEquals(5, PurchaseOrderStatus::CANCELLED->legacyValue());
    }

    public function test_can_edit()
    {
        $this->assertTrue(PurchaseOrderStatus::DRAFT->canEdit());
        $this->assertFalse(PurchaseOrderStatus::WAITING->canEdit());
        $this->assertTrue(PurchaseOrderStatus::REJECTED->canEdit());
        $this->assertFalse(PurchaseOrderStatus::APPROVED->canEdit());
        $this->assertFalse(PurchaseOrderStatus::CANCELLED->canEdit());
    }

    public function test_can_approve()
    {
        $this->assertFalse(PurchaseOrderStatus::DRAFT->canApprove());
        $this->assertTrue(PurchaseOrderStatus::WAITING->canApprove());
        $this->assertFalse(PurchaseOrderStatus::APPROVED->canApprove());
        $this->assertFalse(PurchaseOrderStatus::REJECTED->canApprove());
        $this->assertFalse(PurchaseOrderStatus::CANCELLED->canApprove());
    }

    public function test_is_terminal()
    {
        $this->assertFalse(PurchaseOrderStatus::DRAFT->isTerminal());
        $this->assertFalse(PurchaseOrderStatus::WAITING->isTerminal());
        $this->assertTrue(PurchaseOrderStatus::APPROVED->isTerminal());
        $this->assertTrue(PurchaseOrderStatus::REJECTED->isTerminal());
        $this->assertTrue(PurchaseOrderStatus::CANCELLED->isTerminal());
    }

    public function test_css_classes()
    {
        $this->assertEquals('bg-gray-100 text-gray-800', PurchaseOrderStatus::DRAFT->cssClass());
        $this->assertEquals('bg-yellow-100 text-yellow-800', PurchaseOrderStatus::WAITING->cssClass());
        $this->assertEquals('bg-green-100 text-green-800', PurchaseOrderStatus::APPROVED->cssClass());
        $this->assertEquals('bg-red-100 text-red-800', PurchaseOrderStatus::REJECTED->cssClass());
        $this->assertEquals('bg-orange-100 text-orange-800', PurchaseOrderStatus::CANCELLED->cssClass());
    }

    public function test_from_legacy_value()
    {
        $this->assertEquals(PurchaseOrderStatus::DRAFT, PurchaseOrderStatus::fromLegacyValue(1));
        $this->assertEquals(PurchaseOrderStatus::WAITING, PurchaseOrderStatus::fromLegacyValue(2));
        $this->assertEquals(PurchaseOrderStatus::APPROVED, PurchaseOrderStatus::fromLegacyValue(3));
        $this->assertEquals(PurchaseOrderStatus::REJECTED, PurchaseOrderStatus::fromLegacyValue(4));
        $this->assertEquals(PurchaseOrderStatus::CANCELLED, PurchaseOrderStatus::fromLegacyValue(5));
    }

    public function test_from_legacy_value_invalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        PurchaseOrderStatus::fromLegacyValue(99);
    }

    public function test_values()
    {
        $expected = [1, 2, 3, 4, 5];
        $this->assertEquals($expected, PurchaseOrderStatus::values());
    }

    public function test_options()
    {
        $expected = [
            1 => 'Draft',
            2 => 'Waiting for Approval',
            3 => 'Approved',
            4 => 'Rejected',
            5 => 'Cancelled',
        ];
        $this->assertEquals($expected, PurchaseOrderStatus::options());
    }
}
