<?php

namespace Tests\Unit\Domain\Discipline\Services;

use App\Domain\Discipline\Services\DisciplineDataSyncService;
use Tests\TestCase;

class DisciplineDataSyncServiceTest extends TestCase
{
    private DisciplineDataSyncService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DisciplineDataSyncService;
    }

    /** @test */
    public function it_has_sync_departments_from_employees_method()
    {
        $this->assertTrue(method_exists($this->service, 'syncDepartmentsFromEmployees'));
    }

    /** @test */
    public function it_has_sync_departments_in_weekly_data_method()
    {
        $this->assertTrue(method_exists($this->service, 'syncDepartmentsInWeeklyData'));
    }

    /** @test */
    public function it_has_sync_departments_using_relationships_method()
    {
        $this->assertTrue(method_exists($this->service, 'syncDepartmentsUsingRelationships'));
    }

    /** @test */
    public function it_has_sync_all_departments_method()
    {
        $this->assertTrue(method_exists($this->service, 'syncAllDepartments'));
    }

    /** @test */
    public function sync_departments_from_employees_returns_integer()
    {
        $result = $this->service->syncDepartmentsFromEmployees();

        $this->assertIsInt($result);
    }

    /** @test */
    public function sync_departments_in_weekly_data_returns_integer()
    {
        $result = $this->service->syncDepartmentsInWeeklyData();

        $this->assertIsInt($result);
    }

    /** @test */
    public function sync_departments_using_relationships_returns_integer()
    {
        $result = $this->service->syncDepartmentsUsingRelationships();

        $this->assertIsInt($result);
    }

    /** @test */
    public function sync_all_departments_returns_array_with_statistics()
    {
        $result = $this->service->syncAllDepartments();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('evaluation_data_updated', $result);
        $this->assertArrayHasKey('weekly_data_updated', $result);
        $this->assertArrayHasKey('total_updated', $result);
    }

    /** @test */
    public function sync_all_departments_totals_match_individual_counts()
    {
        $result = $this->service->syncAllDepartments();

        $expectedTotal = $result['evaluation_data_updated'] + $result['weekly_data_updated'];

        $this->assertEquals($expectedTotal, $result['total_updated']);
    }

    /** @test */
    public function all_sync_methods_return_zero_when_no_data_exists()
    {
        // With empty database, all should return 0
        $result1 = $this->service->syncDepartmentsFromEmployees();
        $result2 = $this->service->syncDepartmentsInWeeklyData();
        $result3 = $this->service->syncDepartmentsUsingRelationships();

        $this->assertEquals(0, $result1);
        $this->assertEquals(0, $result2);
        $this->assertEquals(0, $result3);
    }
}
