<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('purchase_requests')->update([
            'workflow_status' => null,
            'workflow_step' => null,
        ]);
    }
};
