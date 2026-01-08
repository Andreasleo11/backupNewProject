<?php

namespace Tests\Unit\Domain\Discipline\Services;

use App\Domain\Discipline\Repositories\EvaluationDataRepository;
use App\Domain\Discipline\Services\DepartmentEmployeeResolver;
use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Tests\TestCase;

class DepartmentEmployeeResolverTest extends TestCase
{
    private DepartmentEmployeeResolver $resolver;

    private EvaluationDataRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock repository
        $this->repository = $this->createMock(EvaluationDataRepository::class);
        $this->resolver = new DepartmentEmployeeResolver($this->repository);
    }

    /** @test */
    public function it_resolves_employees_for_special_user_id_120()
    {
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

        $this->assertCount(2, $employees);
    }

    /** @test */
    public function it_resolves_employees_for_special_email_users()
    {
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

        $this->assertCount(1, $employees);
    }

    /** @test */
    public function it_resolves_employees_for_department_head()
    {
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

        $this->assertCount(1, $employees);
    }

    /** @test */
    public function it_throws_exception_for_non_department_head()
    {
        $dept = new Department(['name' => 'QC']);
        $user = new User([
            'email' => 'regular@daijo.co.id',
            'department' => $dept,
            'is_head' => 0,
        ]);

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage('Only Department Heads can access this');

        $this->resolver->resolveForUser($user);
    }

    /** @test */
    public function it_throws_exception_for_unknown_department()
    {
        $dept = new Department;
        $dept->name = 'UNKNOWN_DEPT';

        $user = new User;
        $user->department = $dept;
        $user->is_head = 1;

        $this->expectException(\DomainException::class);

        $this->resolver->resolveForUser($user);
    }

    /** @test */
    public function it_resolves_logistic_department_with_store()
    {
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

        $this->assertCount(2, $employees);
    }
}
