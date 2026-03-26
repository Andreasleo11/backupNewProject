<?php

use App\Enums\DepartmentCode;

test('it has correct department codes', function () {
    expect(DepartmentCode::ACCOUNTING->value)->toBe('100');
    expect(DepartmentCode::BUSINESS->value)->toBe('200');
    expect(DepartmentCode::PERSONALIA->value)->toBe('310');
    expect(DepartmentCode::PPIC->value)->toBe('311');
    expect(DepartmentCode::PURCHASING->value)->toBe('320');
    expect(DepartmentCode::STORE->value)->toBe('330');
    expect(DepartmentCode::LOGISTIC->value)->toBe('331');
    expect(DepartmentCode::QC->value)->toBe('340');
    expect(DepartmentCode::QA->value)->toBe('341');
    expect(DepartmentCode::MAINTENANCE->value)->toBe('350');
    expect(DepartmentCode::MAINTENANCE_MACHINE->value)->toBe('351');
    expect(DepartmentCode::SECOND_PROCESS->value)->toBe('361');
    expect(DepartmentCode::ASSEMBLY->value)->toBe('362');
    expect(DepartmentCode::MOULDING->value)->toBe('363');
    expect(DepartmentCode::PLASTIC_INJECTION->value)->toBe('390');
    expect(DepartmentCode::PE->value)->toBe('500');
    expect(DepartmentCode::COMPUTER->value)->toBe('600');
});

test('it returns correct labels', function () {
    expect(DepartmentCode::ACCOUNTING->getLabel())->toBe('Accounting');
    expect(DepartmentCode::QC->getLabel())->toBe('QC');
    expect(DepartmentCode::PLASTIC_INJECTION->getLabel())->toBe('Plastic Injection');
    expect(DepartmentCode::MAINTENANCE_MACHINE->getLabel())->toBe('Maintenance Machine');
});

test('it converts from department name', function () {
    expect(DepartmentCode::fromDepartmentName('QC'))->toBe(DepartmentCode::QC);
    expect(DepartmentCode::fromDepartmentName('qc'))->toBe(DepartmentCode::QC);
    expect(DepartmentCode::fromDepartmentName(' QC '))->toBe(DepartmentCode::QC);

    expect(DepartmentCode::fromDepartmentName('ACCOUNTING'))->toBe(DepartmentCode::ACCOUNTING);
    expect(DepartmentCode::fromDepartmentName('PLASTIC INJECTION'))->toBe(DepartmentCode::PLASTIC_INJECTION);
    expect(DepartmentCode::fromDepartmentName('Maintenance Machine'))->toBe(DepartmentCode::MAINTENANCE_MACHINE);
});

test('it returns null for unknown department name', function () {
    expect(DepartmentCode::fromDepartmentName('UNKNOWN_DEPT'))->toBeNull();
    expect(DepartmentCode::fromDepartmentName(''))->toBeNull();
    expect(DepartmentCode::fromDepartmentName('Marketing'))->toBeNull();
});

test('it returns all department values', function () {
    $values = DepartmentCode::values();

    expect($values)->toBeArray();
    expect($values)->toHaveCount(17);
    expect($values)->toContain('100');
    expect($values)->toContain('340');
    expect($values)->toContain('600');
});

test('it has all 17 department codes', function () {
    $cases = DepartmentCode::cases();

    expect($cases)->toHaveCount(17);
});
