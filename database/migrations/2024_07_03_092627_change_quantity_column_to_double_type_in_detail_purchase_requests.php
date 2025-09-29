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
        // First, cast all integer quantity to their corresponding decimal values
        DB::statement('ALTER TABLE detail_purchase_requests MODIFY quantity DECIMAL(15, 2)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally, revert the quantity back to integers
        DB::statement('ALTER TABLE detail_purchase_requests MODIFY quantity INTEGER');
    }
};
