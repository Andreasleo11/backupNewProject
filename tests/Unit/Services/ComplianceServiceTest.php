<?php

namespace Tests\Unit\Services;

use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Models\Requirement;
use App\Models\RequirementAssignment;
use App\Models\RequirementUpload;
use App\Services\ComplianceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplianceServiceTest extends TestCase
{
    use RefreshDatabase;

    private ComplianceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ComplianceService();
    }

    public function test_get_scope_compliance_percent_returns_zero_when_no_requirements_assigned()
    {
        $dept = Department::factory()->create();

        $percent = $this->service->getScopeCompliancePercent($dept);

        $this->assertEquals(0, $percent);
    }

    public function test_get_scope_compliance_percent_only_calculates_mandatory_requirements()
    {
        $dept = Department::factory()->create();

        // 1. Mandatory requirement (OK)
        $req1 = Requirement::create([
            'code' => 'REQ_1',
            'name' => 'Requirement 1',
            'min_count' => 1,
            'requires_approval' => false,
        ]);
        RequirementAssignment::create([
            'requirement_id' => $req1->id,
            'scope_type' => Department::class,
            'scope_id' => $dept->id,
            'is_mandatory' => true,
        ]);
        RequirementUpload::create([
            'requirement_id' => $req1->id,
            'scope_type' => Department::class,
            'scope_id' => $dept->id,
            'path' => 'test.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'status' => 'approved',
        ]);

        // 2. Mandatory requirement (Missing)
        $req2 = Requirement::create([
            'code' => 'REQ_2',
            'name' => 'Requirement 2',
            'min_count' => 1,
            'requires_approval' => false,
        ]);
        RequirementAssignment::create([
            'requirement_id' => $req2->id,
            'scope_type' => Department::class,
            'scope_id' => $dept->id,
            'is_mandatory' => true,
        ]);

        // 3. Optional requirement (Missing)
        $req3 = Requirement::create([
            'code' => 'REQ_3',
            'name' => 'Requirement 3',
            'min_count' => 1,
            'requires_approval' => false,
        ]);
        RequirementAssignment::create([
            'requirement_id' => $req3->id,
            'scope_type' => Department::class,
            'scope_id' => $dept->id,
            'is_mandatory' => false,
        ]);

        // We have 2 mandatory requirements: 1 OK, 1 Missing.
        // Compliance percent should be 50% (1/2). Optional is ignored in calculation.
        $percent = $this->service->getScopeCompliancePercent($dept);
        $this->assertEquals(50, $percent);
    }

    public function test_filters_expired_uploads()
    {
        $dept = Department::factory()->create();

        $req = Requirement::create([
            'code' => 'REQ_1',
            'name' => 'Requirement 1',
            'min_count' => 1,
            'requires_approval' => false,
        ]);
        RequirementAssignment::create([
            'requirement_id' => $req->id,
            'scope_type' => Department::class,
            'scope_id' => $dept->id,
            'is_mandatory' => true,
        ]);

        // Upload that expired yesterday
        RequirementUpload::create([
            'requirement_id' => $req->id,
            'scope_type' => Department::class,
            'scope_id' => $dept->id,
            'path' => 'test.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'status' => 'approved',
            'valid_from' => Carbon::now()->subDays(10),
            'valid_until' => Carbon::now()->subDays(1),
        ]);

        $percent = $this->service->getScopeCompliancePercent($dept);
        $this->assertEquals(0, $percent);

        // Upload valid until tomorrow
        RequirementUpload::create([
            'requirement_id' => $req->id,
            'scope_type' => Department::class,
            'scope_id' => $dept->id,
            'path' => 'test_valid.pdf',
            'original_name' => 'test_valid.pdf',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'status' => 'approved',
            'valid_from' => Carbon::now()->subDays(10),
            'valid_until' => Carbon::now()->addDays(1),
        ]);

        $percent = $this->service->getScopeCompliancePercent($dept);
        $this->assertEquals(100, $percent);
    }

    public function test_requires_approval_constraint()
    {
        $dept = Department::factory()->create();

        $req = Requirement::create([
            'code' => 'REQ_1',
            'name' => 'Requirement 1',
            'min_count' => 1,
            'requires_approval' => true,
        ]);
        RequirementAssignment::create([
            'requirement_id' => $req->id,
            'scope_type' => Department::class,
            'scope_id' => $dept->id,
            'is_mandatory' => true,
        ]);

        // Staged upload in pending state
        $upload = RequirementUpload::create([
            'requirement_id' => $req->id,
            'scope_type' => Department::class,
            'scope_id' => $dept->id,
            'path' => 'test.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'status' => 'pending',
        ]);

        $percent = $this->service->getScopeCompliancePercent($dept);
        $this->assertEquals(0, $percent);

        // Approve the upload
        $upload->update(['status' => 'approved']);

        $percent = $this->service->getScopeCompliancePercent($dept);
        $this->assertEquals(100, $percent);
    }
}
