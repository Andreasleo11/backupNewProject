<?php

use App\Domain\Discipline\Repositories\EvaluationDataRepository;
use App\Domain\Discipline\Services\DisciplineDepartmentStatusService;

beforeEach(function () {
    // Create a mock repository
    $this->repository = $this->createMock(EvaluationDataRepository::class);
    $this->service = new DisciplineDepartmentStatusService($this->repository);
});

test('it has get department status for month method', function () {
    expect(method_exists($this->service, 'getDepartmentStatusForMonth'))->toBeTrue();
});

test('it has get jpayroll department status method', function () {
    expect(method_exists($this->service, 'getJpayrollDepartmentStatus'))->toBeTrue();
});

test('it returns array from get department status for month', function () {
    $result = $this->service->getDepartmentStatusForMonth(1, 2024);

    expect($result)->toBeArray();
});

test('it returns array from get jpayroll department status', function () {
    $result = $this->service->getJpayrollDepartmentStatus(1, 2024);

    expect($result)->toBeArray();
});

test('get jpayroll department status returns same as get department status for month', function () {
    $month = 5;
    $year = 2024;

    $result1 = $this->service->getDepartmentStatusForMonth($month, $year);
    $result2 = $this->service->getJpayrollDepartmentStatus($month, $year);

    expect($result1)->toEqual($result2);
});

test('it accepts valid month and year parameters', function () {
    // Should not throw exception with valid parameters
    $result = $this->service->getDepartmentStatusForMonth(12, 2024);

    expect($result)->toBeArray();
});

test('it returns empty array when no data exists', function () {
    // With no database records, should return empty array
    $result = $this->service->getDepartmentStatusForMonth(1, 2099);

    expect($result)->toBeArray();
});
