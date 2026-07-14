<?php

namespace Tests\Unit\Jobs;

use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Jobs\UpdateDepartmentComplianceSnapshot;
use App\Models\DepartmentComplianceMonthly;
use App\Models\DepartmentComplianceSnapshot;
use App\Models\Requirement;
use App\Models\RequirementAssignment;
use App\Models\RequirementUpload;
use App\Models\User;
use App\Notifications\Compliance\DepartmentBelowThreshold;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UpdateDepartmentComplianceSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_job_updates_realtime_snapshot()
    {
        $dept = Department::factory()->create();

        // Assign a mandatory requirement
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

        // Run job (realtime only)
        UpdateDepartmentComplianceSnapshot::dispatchSync($dept->id, false);

        // Assert snapshot exists at 0%
        $this->assertDatabaseHas('department_compliance_snapshots', [
            'department_id' => $dept->id,
            'percent' => 0,
            'complete_requirements' => 0,
            'total_requirements' => 1,
        ]);

        // Upload valid document
        RequirementUpload::create([
            'requirement_id' => $req->id,
            'scope_type' => Department::class,
            'scope_id' => $dept->id,
            'path' => 'test.pdf',
            'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf',
            'size' => 100,
            'status' => 'approved',
        ]);

        // Run job again
        UpdateDepartmentComplianceSnapshot::dispatchSync($dept->id, false);

        $this->assertDatabaseHas('department_compliance_snapshots', [
            'department_id' => $dept->id,
            'percent' => 100,
            'complete_requirements' => 1,
            'total_requirements' => 1,
        ]);
    }

    public function test_snapshot_job_writes_monthly_historical_log()
    {
        $dept = Department::factory()->create();

        // Assign a mandatory requirement
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

        // Run job with writeMonthly = true
        UpdateDepartmentComplianceSnapshot::dispatchSync($dept->id, true);

        $month = Carbon::now()->startOfMonth()->toDateString();
        $this->assertDatabaseHas('department_compliance_monthlies', [
            'department_id' => $dept->id,
            'month' => $month,
            'percent' => 0,
        ]);
    }

    public function test_snapshot_job_triggers_notification_when_crossing_under_threshold()
    {
        Notification::fake();

        // Create notified users
        $admin = User::factory()->create(['email' => 'raymond@daijo.co.id']);
        $yuli = User::factory()->create(['email' => 'yuli@daijo.co.id']);

        $dept = Department::factory()->create();

        // Assign a mandatory requirement
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

        // Create existing snapshot at 100% compliance
        DepartmentComplianceSnapshot::create([
            'department_id' => $dept->id,
            'percent' => 100,
            'complete_requirements' => 1,
            'total_requirements' => 1,
            'generated_at' => now(),
        ]);

        // Now compliance drops to 0% (because there is no upload, the query calculates 0%)
        UpdateDepartmentComplianceSnapshot::dispatchSync($dept->id, false);

        // Verify snapshot updated
        $this->assertDatabaseHas('department_compliance_snapshots', [
            'department_id' => $dept->id,
            'percent' => 0,
        ]);

        // Assert notification was sent to raymond & yuli
        Notification::assertSentTo(
            [$admin, $yuli],
            DepartmentBelowThreshold::class,
            function ($notification, $channels) use ($dept) {
                return $notification->department->id === $dept->id && $notification->percent === 0;
            }
        );
    }
}
