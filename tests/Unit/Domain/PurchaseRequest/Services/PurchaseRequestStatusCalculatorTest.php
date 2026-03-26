<?php

use App\Domain\PurchaseRequest\Services\PurchaseRequestStatusCalculator;

beforeEach(function () {
    $this->calculator = new PurchaseRequestStatusCalculator;
});

test('it returns draft status when is_draft is true', function () {
    $status = $this->calculator->calculateInitialStatus(
        fromDepartment: 'COMPUTER',
        branch: 'JAKARTA',
        isDraft: true
    );

    expect($status)->toBe(8);
});

test('it returns pending dept head for normal pr', function () {
    $status = $this->calculator->calculateInitialStatus(
        fromDepartment: 'COMPUTER',
        branch: 'JAKARTA',
        isDraft: false
    );

    expect($status)->toBe(1);
});

test('it returns pending gm for plastic injection', function () {
    $status = $this->calculator->calculateInitialStatus(
        fromDepartment: 'PLASTIC INJECTION',
        branch: 'JAKARTA',
        isDraft: false
    );

    expect($status)->toBe(7);
});

test('it returns pending gm for maintenance machine in karawang', function () {
    $status = $this->calculator->calculateInitialStatus(
        fromDepartment: 'MAINTENANCE MACHINE',
        branch: 'KARAWANG',
        isDraft: false
    );

    expect($status)->toBe(7);
});

test('it returns pending dept head for maintenance machine in jakarta', function () {
    $status = $this->calculator->calculateInitialStatus(
        fromDepartment: 'MAINTENANCE MACHINE',
        branch: 'JAKARTA',
        isDraft: false
    );

    expect($status)->toBe(1);
});

test('it returns pending purchaser for personalia', function () {
    $status = $this->calculator->calculateInitialStatus(
        fromDepartment: 'PERSONALIA',
        branch: 'JAKARTA',
        isDraft: false
    );

    expect($status)->toBe(6);
});

test('it identifies pending statuses', function () {
    expect($this->calculator->isPending(1))->toBeTrue();
    expect($this->calculator->isPending(2))->toBeTrue();
    expect($this->calculator->isPending(3))->toBeTrue();
    expect($this->calculator->isPending(6))->toBeTrue();
    expect($this->calculator->isPending(7))->toBeTrue();

    expect($this->calculator->isPending(0))->toBeFalse();
    expect($this->calculator->isPending(4))->toBeFalse();
    expect($this->calculator->isPending(5))->toBeFalse();
    expect($this->calculator->isPending(8))->toBeFalse();
});

test('it identifies completed status', function () {
    expect($this->calculator->isCompleted(4))->toBeTrue();
    expect($this->calculator->isCompleted(1))->toBeFalse();
    expect($this->calculator->isCompleted(5))->toBeFalse();
});

test('it identifies rejected status', function () {
    expect($this->calculator->isRejected(5))->toBeTrue();
    expect($this->calculator->isRejected(1))->toBeFalse();
    expect($this->calculator->isRejected(4))->toBeFalse();
});

test('it identifies draft or cancelled status', function () {
    expect($this->calculator->isDraftOrCancelled(0))->toBeTrue();
    expect($this->calculator->isDraftOrCancelled(8))->toBeTrue();
    expect($this->calculator->isDraftOrCancelled(1))->toBeFalse();
    expect($this->calculator->isDraftOrCancelled(4))->toBeFalse();
});

test('it returns correct status text', function () {
    expect($this->calculator->getStatusText(0))->toBe('Draft');
    expect($this->calculator->getStatusText(1))->toBe('Pending Department Head');
    expect($this->calculator->getStatusText(2))->toBe('Pending Verificator');
    expect($this->calculator->getStatusText(3))->toBe('Pending Director');
    expect($this->calculator->getStatusText(4))->toBe('Approved');
    expect($this->calculator->getStatusText(5))->toBe('Rejected');
    expect($this->calculator->getStatusText(6))->toBe('Pending Purchaser');
    expect($this->calculator->getStatusText(7))->toBe('Pending GM');
    expect($this->calculator->getStatusText(8))->toBe('Cancelled/Draft');
    expect($this->calculator->getStatusText(999))->toBe('Unknown');
});
