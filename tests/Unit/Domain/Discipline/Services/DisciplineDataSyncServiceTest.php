<?php

use App\Domain\Discipline\Services\DisciplineDataSyncService;

beforeEach(function () {
    $this->service = new DisciplineDataSyncService;
});

test('it has sync departments from employees method', function () {
    expect(method_exists($this->service, 'syncDepartmentsFromEmployees'))->toBeTrue();
});

test('it has sync departments in weekly data method', function () {
    expect(method_exists($this->service, 'syncDepartmentsInWeeklyData'))->toBeTrue();
});

test('it has sync departments using relationships method', function () {
    expect(method_exists($this->service, 'syncDepartmentsUsingRelationships'))->toBeTrue();
});

test('it has sync all departments method', function () {
    expect(method_exists($this->service, 'syncAllDepartments'))->toBeTrue();
});

test('sync departments from employees returns integer', function () {
    $result = $this->service->syncDepartmentsFromEmployees();

    expect($result)->toBeInt();
});

test('sync departments in weekly data returns integer', function () {
    $result = $this->service->syncDepartmentsInWeeklyData();

    expect($result)->toBeInt();
});

test('sync departments using relationships returns integer', function () {
    $result = $this->service->syncDepartmentsUsingRelationships();

    expect($result)->toBeInt();
});

test('sync all departments returns array with statistics', function () {
    $result = $this->service->syncAllDepartments();

    expect($result)->toBeArray();
    expect($result)->toHaveKey('evaluation_data_updated');
    expect($result)->toHaveKey('weekly_data_updated');
    expect($result)->toHaveKey('total_updated');
});

test('sync all departments totals match individual counts', function () {
    $result = $this->service->syncAllDepartments();

    $expectedTotal = $result['evaluation_data_updated'] + $result['weekly_data_updated'];

    expect($result['total_updated'])->toBe($expectedTotal);
});

test('all sync methods return zero when no data exists', function () {
    // With empty database, all should return 0
    $result1 = $this->service->syncDepartmentsFromEmployees();
    $result2 = $this->service->syncDepartmentsInWeeklyData();
    $result3 = $this->service->syncDepartmentsUsingRelationships();

    expect($result1)->toBe(0);
    expect($result2)->toBe(0);
    expect($result3)->toBe(0);
});
