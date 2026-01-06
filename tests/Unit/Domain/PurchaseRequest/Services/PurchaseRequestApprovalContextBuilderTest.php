<?php

use App\Domain\PurchaseRequest\Services\PurchaseRequestApprovalContextBuilder;

beforeEach(function () {
    $this->builder = new PurchaseRequestApprovalContextBuilder;
});

test('it builds approval context with all required fields', function () {
    $items = [
        ['price' => 100, 'quantity' => 2],
        ['price' => 50, 'quantity' => 3],
    ];

    $context = $this->builder->build(
        fromDepartment: 'COMPUTER',
        toDepartment: 'PURCHASING',
        branch: 'JAKARTA',
        isOffice: true,
        items: $items
    );

    expect($context)
        ->toHaveKey('from_department')
        ->toHaveKey('to_department')
        ->toHaveKey('branch')
        ->toHaveKey('at_office')
        ->toHaveKey('amount');

    expect($context['from_department'])->toBe('COMPUTER');
    expect($context['to_department'])->toBe('PURCHASING');
    expect($context['branch'])->toBe('JAKARTA');
    expect($context['at_office'])->toBeTrue();
});

test('it calculates total amount correctly', function () {
    $items = [
        ['price' => 100.50, 'quantity' => 2],   // 201
        ['price' => 50.25, 'quantity' => 3],    // 150.75
    ];

    $context = $this->builder->build(
        fromDepartment: 'COMPUTER',
        toDepartment: 'PURCHASING',
        branch: 'JAKARTA',
        isOffice: true,
        items: $items
    );

    expect($context['amount'])->toBe(351.75);
});

test('it handles empty items array', function () {
    $context = $this->builder->build(
        fromDepartment: 'COMPUTER',
        toDepartment: 'PURCHASING',
        branch: 'JAKARTA',
        isOffice: true,
        items: []
    );

    expect($context['amount'])->toBe(0.0);
});

test('it converts department names to uppercase', function () {
    $context = $this->builder->build(
        fromDepartment: 'computer',
        toDepartment: 'purchasing',
        branch: 'jakarta',
        isOffice: true,
        items: []
    );

    expect($context['from_department'])->toBe('COMPUTER');
    expect($context['to_department'])->toBe('PURCHASING');
    expect($context['branch'])->toBe('JAKARTA');
});

test('it handles items with missing price or quantity', function () {
    $items = [
        ['price' => 100, 'quantity' => 2],
        ['price' => null, 'quantity' => 3],  // Missing price
        ['price' => 50, 'quantity' => null], // Missing quantity
        [],                                   // Empty item
    ];

    $context = $this->builder->build(
        fromDepartment: 'COMPUTER',
        toDepartment: 'PURCHASING',
        branch: 'JAKARTA',
        isOffice: true,
        items: $items
    );

    // Only first item should be calculated: 100 * 2 = 200
    expect($context['amount'])->toBe(200.0);
});

test('it handles items as objects', function () {
    $items = [
        (object) ['price' => 100, 'quantity' => 2],
        (object) ['price' => 50, 'quantity' => 3],
    ];

    $context = $this->builder->build(
        fromDepartment: 'COMPUTER',
        toDepartment: 'PURCHASING',
        branch: 'JAKARTA',
        isOffice: true,
        items: $items
    );

    expect($context['amount'])->toBe(350.0);
});

test('it sets at_office to false for factory', function () {
    $context = $this->builder->build(
        fromDepartment: 'MOULDING',
        toDepartment: 'PURCHASING',
        branch: 'KARAWANG',
        isOffice: false,
        items: []
    );

    expect($context['at_office'])->toBeFalse();
});
