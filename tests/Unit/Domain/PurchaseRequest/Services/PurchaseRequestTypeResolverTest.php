<?php

use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use App\Domain\PurchaseRequest\Services\PurchaseRequestTypeResolver;

beforeEach(function () {
    $this->repositoryMock = Mockery::mock(PurchaseRequestRepository::class);
    $this->resolver = new PurchaseRequestTypeResolver($this->repositoryMock);
});

afterEach(function () {
    Mockery::close();
});

test('it returns factory for pe department', function () {
    // PE is always factory, no need to check office departments
    $this->repositoryMock->shouldNotReceive('getOfficeDepartmentNames');

    $type = $this->resolver->resolve('PE');

    expect($type)->toBe('factory');
});

test('it returns office for office departments', function () {
    $this->repositoryMock
        ->shouldReceive('getOfficeDepartmentNames')
        ->once()
        ->andReturn(['COMPUTER', 'PERSONALIA', 'PURCHASING']);

    $type = $this->resolver->resolve('COMPUTER');

    expect($type)->toBe('office');
});

test('it returns factory for non-office departments', function () {
    $this->repositoryMock
        ->shouldReceive('getOfficeDepartmentNames')
        ->once()
        ->andReturn(['COMPUTER', 'PERSONALIA', 'PURCHASING']);

    $type = $this->resolver->resolve('MOULDING');

    expect($type)->toBe('factory');
});

test('it is case insensitive', function () {
    $this->repositoryMock
        ->shouldReceive('getOfficeDepartmentNames')
        ->once()
        ->andReturn(['COMPUTER', 'PERSONALIA']);

    $type = $this->resolver->resolve('computer');

    expect($type)->toBe('office');
});

test('is_office_department returns true for office', function () {
    $this->repositoryMock
        ->shouldReceive('getOfficeDepartmentNames')
        ->andReturn(['COMPUTER']);

    expect($this->resolver->isOfficeDepartment('COMPUTER'))->toBeTrue();
});

test('is_office_department returns false for factory', function () {
    $this->repositoryMock
        ->shouldReceive('getOfficeDepartmentNames')
        ->andReturn(['COMPUTER']);

    expect($this->resolver->isOfficeDepartment('MOULDING'))->toBeFalse();
});

test('is_factory_department returns true for factory', function () {
    $this->repositoryMock
        ->shouldReceive('getOfficeDepartmentNames')
        ->andReturn(['COMPUTER']);

    expect($this->resolver->isFactoryDepartment('MOULDING'))->toBeTrue();
});

test('is_factory_department returns false for office', function () {
    $this->repositoryMock
        ->shouldReceive('getOfficeDepartmentNames')
        ->andReturn(['COMPUTER']);

    expect($this->resolver->isFactoryDepartment('COMPUTER'))->toBeFalse();
});
