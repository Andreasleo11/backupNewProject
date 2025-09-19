<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, cast all integer prices to their corresponding decimal values
        DB::statement(
            "ALTER TABLE monthly_budget_report_summary_details MODIFY cost_per_unit DECIMAL(15, 2)",
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally, revert the prices back to integers
        DB::statement(
            "ALTER TABLE monthly_budget_report_summary_details MODIFY cost_per_unit INTEGER",
        );
    }
};
