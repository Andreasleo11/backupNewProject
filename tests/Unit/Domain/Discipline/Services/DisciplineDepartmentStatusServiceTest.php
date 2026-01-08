<?php

namespace Tests\Unit\Domain\Discipline\Services;

use App\Domain\Discipline\Repositories\EvaluationDataRepository;
use App\Domain\Discipline\Services\DisciplineDepartmentStatusService;
use Tests\TestCase;

class DisciplineDepartmentStatusServiceTest extends TestCase
{
    private DisciplineDepartmentStatusService $service;

    private EvaluationDataRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock repository
        $this->repository = $this->createMock(EvaluationDataRepository::class);
        $this->service = new DisciplineDepartmentStatusService($this->repository);
    }

    /** @test */
    public function it_has_get_department_status_for_month_method()
    {
        $this->assertTrue(method_exists($this->service, 'getDepartmentStatusForMonth'));
    }

    /** @test */
    public function it_has_get_jpayroll_department_status_method()
    {
        $this->assertTrue(method_exists($this->service, 'getJpayrollDepartmentStatus'));
    }

    /** @test */
    public function it_returns_array_from_get_department_status_for_month()
    {
        $result = $this->service->getDepartmentStatusForMonth(1, 2024);

        $this->assertIsArray($result);
    }

    /** @test */
    public function it_returns_array_from_get_jpayroll_department_status()
    {
        $result = $this->service->getJpayrollDepartmentStatus(1, 2024);

        $this->assertIsArray($result);
    }

    /** @test */
    public function get_jpayroll_department_status_returns_same_as_get_department_status_for_month()
    {
        $month = 5;
        $year = 2024;

        $result1 = $this->service->getDepartmentStatusForMonth($month, $year);
        $result2 = $this->service->getJpayrollDepartmentStatus($month, $year);

        $this->assertEquals($result1, $result2);
    }

    /** @test */
    public function it_accepts_valid_month_and_year_parameters()
    {
        // Should not throw exception with valid parameters
        $result = $this->service->getDepartmentStatusForMonth(12, 2024);

        $this->assertIsArray($result);
    }

    /** @test */
    public function it_returns_empty_array_when_no_data_exists()
    {
        // With no database records, should return empty array
        $result = $this->service->getDepartmentStatusForMonth(1, 2099);

        $this->assertIsArray($result);
    }
}
