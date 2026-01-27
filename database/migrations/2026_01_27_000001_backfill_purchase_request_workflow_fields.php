<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('purchase_requests')->orderBy('id')->chunk(100, function ($prs) {
            foreach ($prs as $pr) {
                $update = $this->mapStatusToWorkflow($pr->status);
                if ($update) {
                    DB::table('purchase_requests')
                        ->where('id', $pr->id)
                        ->update($update);
                }
            }
        });
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

    private function mapStatusToWorkflow(int $status): ?array
    {
        return match ($status) {
            0 => ['workflow_status' => 'DRAFT', 'workflow_step' => null],
            1 => ['workflow_status' => 'IN_REVIEW', 'workflow_step' => 'Pending Dept Head'],
            2 => ['workflow_status' => 'IN_REVIEW', 'workflow_step' => 'Pending Verificator'],
            3 => ['workflow_status' => 'IN_REVIEW', 'workflow_step' => 'Pending Director'],
            4 => ['workflow_status' => 'APPROVED', 'workflow_step' => null],
            5 => ['workflow_status' => 'REJECTED', 'workflow_step' => null],
            6 => ['workflow_status' => 'IN_REVIEW', 'workflow_step' => 'Pending Purchaser'],
            7 => ['workflow_status' => 'IN_REVIEW', 'workflow_step' => 'Pending GM'],
            8 => ['workflow_status' => 'CANCELED', 'workflow_step' => null],
            default => null,
        };
    }
};
