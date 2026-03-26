<?php

use App\Domain\Discipline\Services\DisciplineApprovalService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new DisciplineApprovalService;

    // Create a test user
    $this->user = new User;
    $this->user->name = 'Test Manager';
    $this->user->email = 'manager@test.com';

    // Authenticate the user
    Auth::shouldReceive('user')->andReturn($this->user);
});

test('it has approve dept head method', function () {
    expect(method_exists($this->service, 'approveDeptHead'))->toBeTrue();
});

test('it has approve general manager method', function () {
    expect(method_exists($this->service, 'approveGeneralManager'))->toBeTrue();
});

test('it has reject dept head method', function () {
    expect(method_exists($this->service, 'rejectDeptHead'))->toBeTrue();
});

test('it has reject hrd method', function () {
    expect(method_exists($this->service, 'rejectHRD'))->toBeTrue();
});

test('it returns integer count from approve dept head', function () {
    $result = $this->service->approveDeptHead('310', 5, 2024);

    expect($result)->toBeInt();
});

test('it returns integer count from approve general manager', function () {
    $result = $this->service->approveGeneralManager('310', 6, 2024);

    expect($result)->toBeInt();
});

test('it returns integer count from reject dept head', function () {
    $result = $this->service->rejectDeptHead('310', 7, 2024);

    expect($result)->toBeInt();
});

test('it returns integer count from reject hrd', function () {
    $result = $this->service->rejectHRD('310', 8, 2024);

    expect($result)->toBeInt();
});

test('it accepts lock data parameter in approve dept head', function () {
    // Should not throw exception with lockData parameter
    $result = $this->service->approveDeptHead('310', 5, 2024, lockData: true);

    expect($result)->toBeInt();
});

test('it accepts year parameter in approve general manager', function () {
    // Should work with year
    $result1 = $this->service->approveGeneralManager('310', 6, 2024);
    expect($result1)->toBeInt();

    // Should work without year (null)
    $result2 = $this->service->approveGeneralManager('310', 6);
    expect($result2)->toBeInt();
});

test('it accepts optional remark in reject dept head', function () {
    // With remark
    $result1 = $this->service->rejectDeptHead('310', 7, 2024, 'Test remark');
    expect($result1)->toBeInt();

    // Without remark
    $result2 = $this->service->rejectDeptHead('310', 7, 2024);
    expect($result2)->toBeInt();
});

test('it accepts optional remark in reject hrd', function () {
    // With remark
    $result1 = $this->service->rejectHRD('310', 8, 2024, 'Test remark');
    expect($result1)->toBeInt();

    // Without remark
    $result2 = $this->service->rejectHRD('310', 8, 2024);
    expect($result2)->toBeInt();
});

test('it returns zero when no matching records', function () {
    // Using a department and date that don't exist
    $result = $this->service->approveDeptHead('99999', 12, 2099);

    expect($result)->toBe(0);
});
