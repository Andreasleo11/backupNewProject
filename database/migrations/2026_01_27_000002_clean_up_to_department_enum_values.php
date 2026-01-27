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
        // Standardize to case-sensitive backing values in App\Enums\ToDepartment

        // Personnel / Personalia
        DB::table('purchase_requests')
            ->whereIn(DB::raw('LOWER(to_department)'), ['personnel', 'personalia'])
            ->update(['to_department' => 'Personnel']);

        // Maintenance
        DB::table('purchase_requests')
            ->where(DB::raw('LOWER(to_department)'), 'maintenance')
            ->update(['to_department' => 'Maintenance']);

        // Computer
        DB::table('purchase_requests')
            ->where(DB::raw('LOWER(to_department)'), 'computer')
            ->update(['to_department' => 'Computer']);

        // Purchasing
        DB::table('purchase_requests')
            ->where(DB::raw('LOWER(to_department)'), 'purchasing')
            ->update(['to_department' => 'Purchasing']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy way to reverse standardizing casing without potentially corrupting data,
        // but we can leave it as is since title-case is generally preferred.
    }
};
