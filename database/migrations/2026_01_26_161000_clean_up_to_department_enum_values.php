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
        // Personnel / Personalia cleanup
        DB::table('purchase_requests')
            ->whereIn('to_department', ['personalia', 'personnel', 'PERSONNEL', 'PERSONALIA'])
            ->update(['to_department' => 'Personnel']);

        // Maintenance cleanup
        DB::table('purchase_requests')
            ->whereIn('to_department', ['maintenance', 'MAINTENANCE'])
            ->update(['to_department' => 'Maintenance']);

        // Computer cleanup
        DB::table('purchase_requests')
            ->whereIn('to_department', ['computer', 'COMPUTER'])
            ->update(['to_department' => 'Computer']);

        // Purchasing cleanup
        DB::table('purchase_requests')
            ->whereIn('to_department', ['purchasing', 'PURCHASING'])
            ->update(['to_department' => 'Purchasing']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: we want to keep the cleaned up data
    }
};
