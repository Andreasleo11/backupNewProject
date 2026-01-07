<?php

namespace Tests\Unit\Domain\Discipline\Services;

use App\Domain\Discipline\Services\DisciplineApprovalService;
use App\Models\Employee;
use App\Models\EvaluationData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DisciplineApprovalServiceTest extends TestCase
{
    use RefreshDatabase;

    private DisciplineApprovalService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DisciplineApprovalService;

        // Create a test user
        $this->user = new User();
        $this->user->name = 'Test Manager';
        $this->user->email = 'manager@test.com';

        // Authenticate the user
        Auth::shouldReceive('user')->andReturn($this->user);
    }

    /** @test */
    public function it_has_approve_dept_head_method()
    {
        $this->assertTrue(method_exists($this->service, 'approveDeptHead'));
    }

    /** @test */
    public function it_has_approve_general_manager_method()
    {
        $this->assertTrue(method_exists($this->service, 'approveGeneralManager'));
    }

    /** @test */
    public function it_has_reject_dept_head_method()
    {
        $this->assertTrue(method_exists($this->service, 'rejectDeptHead'));
    }

    /** @test */
    public function it_has_reject_hrd_method()
    {
        $this->assertTrue(method_exists($this->service, 'rejectHRD'));
    }

    /** @test */
    public function it_returns_integer_count_from_approve_dept_head()
    {
        $result = $this->service->approveDeptHead('310', 5, 2024);

        $this->assertIsInt($result);
    }

    /** @test */
    public function it_returns_integer_count_from_approve_general_manager()
    {
        $result = $this->service->approveGeneralManager('310', 6, 2024);

        $this->assertIsInt($result);
    }

    /** @test */
    public function it_returns_integer_count_from_reject_dept_head()
    {
        $result = $this->service->rejectDeptHead('310', 7, 2024);

        $this->assertIsInt($result);
    }

    /** @test */
    public function it_returns_integer_count_from_reject_hrd()
    {
        $result = $this->service->rejectHRD('310', 8, 2024);

        $this->assertIsInt($result);
    }

    /** @test */
    public function it_accepts_lock_data_parameter_in_approve_dept_head()
    {
        // Should not throw exception with lockData parameter
        $result = $this->service->approveDeptHead('310', 5, 2024, lockData: true);

        $this->assertIsInt($result);
    }

    /** @test */
    public function it_accepts_year_parameter_in_approve_general_manager()
    {
        // Should work with year
        $result1 = $this->service->approveGeneralManager('310', 6, 2024);
        $this->assertIsInt($result1);

        // Should work without year (null)
        $result2 = $this->service->approveGeneralManager('310', 6);
        $this->assertIsInt($result2);
    }

    /** @test */
    public function it_accepts_optional_remark_in_reject_dept_head()
    {
        // With remark
        $result1 = $this->service->rejectDeptHead('310', 7, 2024, 'Test remark');
        $this->assertIsInt($result1);

        // Without remark
        $result2 = $this->service->rejectDeptHead('310', 7, 2024);
        $this->assertIsInt($result2);
    }

    /** @test */
    public function it_accepts_optional_remark_in_reject_hrd()
    {
        // With remark
        $result1 = $this->service->rejectHRD('310', 8, 2024, 'Test remark');
        $this->assertIsInt($result1);

        // Without remark
        $result2 = $this->service->rejectHRD('310', 8, 2024);
        $this->assertIsInt($result2);
    }

    /** @test */
    public function it_returns_zero_when_no_matching_records()
    {
        // Using a department and date that don't exist
        $result = $this->service->approveDeptHead('99999', 12, 2099);

        $this->assertEquals(0, $result);
    }
}
