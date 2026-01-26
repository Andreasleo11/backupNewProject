<?php

use App\Domain\Discipline\Repositories\EvaluationDataRepository;
use App\Domain\Discipline\Services\DepartmentEmployeeResolver;
use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

beforeEach(function () {
    // Create a mock repository
    $this->repository = $this->createMock(EvaluationDataRepository::class);
    $this->resolver = new DepartmentEmployeeResolver($this->repository);
});

test('it resolves employees for special user id 120', function () {
    $dept = new Department;
    $dept->name = 'QC';

    $user = new User;
    $user->id = 120;
    $user->department = $dept;

    // Mock repository to return empty collection
    $this->repository->expects($this->once())
        ->method('getByDepartmentCodes')
        ->with(['340', '341'])
        ->willReturn(new Collection([
            (object) ['id' => 1, 'name' => 'Employee 1'],
            (object) ['id' => 2, 'name' => 'Employee 2'],
        ]));

    $employees = $this->resolver->resolveForUser($user);

    expect($employees)->toHaveCount(2);
});

test('it resolves employees for special email users', function () {
    $dept = new Department(['name' => 'Personalia']);
    $user = new User([
        'email' => 'ani_apriani@daijo.co.id',
        'department' => $dept,
        'is_head' => 0,
    ]);

    $this->repository->expects($this->once())
        ->method('getByDepartmentCodes')
        ->with(['310'])
        ->willReturn(new Collection([
            (object) ['id' => 1, 'name' => 'Employee 1'],
        ]));

    $employees = $this->resolver->resolveForUser($user);

    expect($employees)->toHaveCount(1);
});

test('it resolves employees for department head', function () {
    $dept = new Department;
    $dept->name = 'QC';

    $user = new User;
    $user->department = $dept;
    $user->is_head = 1;

    $this->repository->expects($this->once())
        ->method('getByDepartmentCodes')
        ->with(['340'])
        ->willReturn(new Collection([
            (object) ['id' => 1, 'name' => 'Employee 1'],
        ]));

    $employees = $this->resolver->resolveForUser($user);

    expect($employees)->toHaveCount(1);
});

test('it throws exception for non department head', function () {
    $dept = new Department(['name' => 'QC']);
    $user = new User([
        'email' => 'regular@daijo.co.id',
        'department' => $dept,
        'is_head' => 0,
    ]);

    $this->expectException(AuthorizationException::class);
    $this->expectExceptionMessage('Only Department Heads can access this');

    $this->resolver->resolveForUser($user);
});

test('it throws exception for unknown department', function () {
    $dept = new Department;
    $dept->name = 'UNKNOWN_DEPT';

    $user = new User;
    $user->department = $dept;
    $user->is_head = 1;

    $this->expectException(\DomainException::class);

    $this->resolver->resolveForUser($user);
});

test('it resolves logistic department with store', function () {
    $dept = new Department;
    $dept->name = 'Logistic';

    $user = new User;
    $user->department = $dept;
    $user->is_head = 1;

    $this->repository->expects($this->once())
        ->method('getByDepartmentCodes')
        ->with(['331', '330']) // Should get both Logistic (331) and Store (330)
        ->willReturn(new Collection([
            (object) ['id' => 1, 'dept' => '331'],
            (object) ['id' => 2, 'dept' => '330'],
        ]));

    $employees = $this->resolver->resolveForUser($user);

    expect($employees)->toHaveCount(2);
});
