<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Normalize VerificationReport
        DB::table('approval_requests')
            ->where('approvable_type', 'App\\Infrastructure\\Persistence\\Eloquent\\Models\\VerificationReport')
            ->update(['approvable_type' => 'verification_report']);

        // 2. Normalize OvertimeForm
        DB::table('approval_requests')
            ->where('approvable_type', 'App\\Domain\\Overtime\\Models\\OvertimeForm')
            ->update(['approvable_type' => 'overtime']);

        // 3. Normalize PurchaseRequest
        DB::table('approval_requests')
            ->where('approvable_type', 'App\\Models\\PurchaseRequest')
            ->update(['approvable_type' => 'pr']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revert VerificationReport
        DB::table('approval_requests')
            ->where('approvable_type', 'verification_report')
            ->update(['approvable_type' => 'App\\Infrastructure\\Persistence\\Eloquent\\Models\\VerificationReport']);

        // 2. Revert OvertimeForm
        DB::table('approval_requests')
            ->where('approvable_type', 'overtime')
            ->update(['approvable_type' => 'App\\Domain\\Overtime\\Models\\OvertimeForm']);

        // 3. Revert PurchaseRequest
        DB::table('approval_requests')
            ->where('approvable_type', 'pr')
            ->update(['approvable_type' => 'App\\Models\\PurchaseRequest']);
    }
};
