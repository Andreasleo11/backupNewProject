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
        // First, cast all integer prices to their corresponding decimal values
        DB::statement('ALTER TABLE detail_purchase_requests MODIFY price_before DECIMAL(15, 2)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally, revert the prices back to integers
        DB::statement('ALTER TABLE detail_purchase_requests MODIFY price_before INTEGER');
    }
};
