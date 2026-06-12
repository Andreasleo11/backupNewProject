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
        // First, cast all integer prices to their corresponding decimal values
        DB::statement('ALTER TABLE detail_purchase_requests MODIFY price DECIMAL(15, 2)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement('ALTER TABLE detail_purchase_requests MODIFY price INTEGER');
        } catch (\Exception $e) {
            // Ignore if data is out of range for INTEGER
        }
    }
};
