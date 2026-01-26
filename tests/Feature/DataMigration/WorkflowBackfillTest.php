<?php

namespace Tests\Feature\DataMigration;

use App\Enums\ToDepartment;
use App\Models\PurchaseRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WorkflowBackfillTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_backfills_workflow_fields_based_on_legacy_status()
    {
        // 1. Create a user to avoid foreign key issues
        $user = \App\Models\User::factory()->create();

        // 2. Seed data with legacy status but NO workflow fields
        $prId = DB::table('purchase_requests')->insertGetId([
            'status' => 4, // Approved
            'to_department' => 'Personnel',
            'user_id_create' => $user->id,
            'date_pr' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Run the backfill logic
        $this->runBackfill();

        // 4. Assert
        $pr = DB::table('purchase_requests')->where('id', $prId)->first();
        $this->assertEquals('APPROVED', $pr->workflow_status);
        $this->assertEquals('FINISHED', $pr->workflow_step);
    }

    /** @test */
    public function it_cleans_up_to_department_enum_casing()
    {
        // 1. Create a user
        $user = \App\Models\User::factory()->create();

        // 2. Seed with bad casing
        $prId = DB::table('purchase_requests')->insertGetId([
            'status' => 0,
            'to_department' => 'personnel', // Should be 'Personnel'
            'user_id_create' => $user->id,
            'date_pr' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Run cleanup logic
        $this->runEnumCleanup();

        // 3. Assert
        $pr = PurchaseRequest::find($prId);
        $this->assertEquals(ToDepartment::PERSONALIA, $pr->to_department);
        $this->assertEquals('Personnel', $pr->getRawOriginal('to_department'));
    }

    private function runBackfill()
    {
        DB::table('purchase_requests')->orderBy('id')->chunkById(200, function ($prs) {
            foreach ($prs as $pr) {
                $workflowStatus = $this->mapStatus($pr->status);
                $workflowStep = $this->mapStep($pr->status);

                DB::table('purchase_requests')
                    ->where('id', $pr->id)
                    ->update([
                        'workflow_status' => $workflowStatus,
                        'workflow_step' => $workflowStep,
                    ]);
            }
        });
    }

    private function mapStatus(int $status): string
    {
        return match ($status) {
            0, 8 => 'DRAFT',
            4 => 'APPROVED',
            5 => 'REJECTED',
            default => 'IN_REVIEW',
        };
    }

    private function mapStep(int $status): ?string
    {
        return match ($status) {
            1 => 'DEPT_HEAD',
            2 => 'VERIFICATOR',
            3 => 'DIRECTOR',
            4 => 'FINISHED',
            5 => 'REJECTED',
            6 => 'PURCHASER',
            7 => 'GM',
            default => null,
        };
    }

    private function runEnumCleanup()
    {
        DB::table('purchase_requests')
            ->whereIn('to_department', ['personalia', 'personnel', 'PERSONNEL', 'PERSONALIA'])
            ->update(['to_department' => 'Personnel']);
    }
}
