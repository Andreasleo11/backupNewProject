<?php

namespace Tests\Unit\Enums;

use App\Enums\DepartmentCode;
use PHPUnit\Framework\TestCase;

class DepartmentCodeTest extends TestCase
{
    /** @test */
    public function it_has_correct_department_codes()
    {
        $this->assertEquals('100', DepartmentCode::ACCOUNTING->value);
        $this->assertEquals('200', DepartmentCode::BUSINESS->value);
        $this->assertEquals('310', DepartmentCode::PERSONALIA->value);
        $this->assertEquals('311', DepartmentCode::PPIC->value);
        $this->assertEquals('320', DepartmentCode::PURCHASING->value);
        $this->assertEquals('330', DepartmentCode::STORE->value);
        $this->assertEquals('331', DepartmentCode::LOGISTIC->value);
        $this->assertEquals('340', DepartmentCode::QC->value);
        $this->assertEquals('341', DepartmentCode::QA->value);
        $this->assertEquals('350', DepartmentCode::MAINTENANCE->value);
        $this->assertEquals('351', DepartmentCode::MAINTENANCE_MACHINE->value);
        $this->assertEquals('361', DepartmentCode::SECOND_PROCESS->value);
        $this->assertEquals('362', DepartmentCode::ASSEMBLY->value);
        $this->assertEquals('363', DepartmentCode::MOULDING->value);
        $this->assertEquals('390', DepartmentCode::PLASTIC_INJECTION->value);
        $this->assertEquals('500', DepartmentCode::PE->value);
        $this->assertEquals('600', DepartmentCode::COMPUTER->value);
    }

    /** @test */
    public function it_returns_correct_labels()
    {
        $this->assertEquals('Accounting', DepartmentCode::ACCOUNTING->getLabel());
        $this->assertEquals('QC', DepartmentCode::QC->getLabel());
        $this->assertEquals('Plastic Injection', DepartmentCode::PLASTIC_INJECTION->getLabel());
        $this->assertEquals('Maintenance Machine', DepartmentCode::MAINTENANCE_MACHINE->getLabel());
    }

    /** @test */
    public function it_converts_from_department_name()
    {
        $this->assertEquals(DepartmentCode::QC, DepartmentCode::fromDepartmentName('QC'));
        $this->assertEquals(DepartmentCode::QC, DepartmentCode::fromDepartmentName('qc'));
        $this->assertEquals(DepartmentCode::QC, DepartmentCode::fromDepartmentName(' QC '));

        $this->assertEquals(DepartmentCode::ACCOUNTING, DepartmentCode::fromDepartmentName('ACCOUNTING'));
        $this->assertEquals(DepartmentCode::PLASTIC_INJECTION, DepartmentCode::fromDepartmentName('PLASTIC INJECTION'));
        $this->assertEquals(DepartmentCode::MAINTENANCE_MACHINE, DepartmentCode::fromDepartmentName('Maintenance Machine'));
    }

    /** @test */
    public function it_returns_null_for_unknown_department_name()
    {
        $this->assertNull(DepartmentCode::fromDepartmentName('UNKNOWN_DEPT'));
        $this->assertNull(DepartmentCode::fromDepartmentName(''));
        $this->assertNull(DepartmentCode::fromDepartmentName('Marketing'));
    }

    /** @test */
    public function it_returns_all_department_values()
    {
        $values = DepartmentCode::values();

        $this->assertIsArray($values);
        $this->assertCount(17, $values);
        $this->assertContains('100', $values);
        $this->assertContains('340', $values);
        $this->assertContains('600', $values);
    }

    /** @test */
    public function it_has_all_17_department_codes()
    {
        $cases = DepartmentCode::cases();

        $this->assertCount(17, $cases);
    }
}
