<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace legacy report_id / detail_id FKs (pointing at `reports` / `details`) with
     * new verification_report_id / verification_item_id FKs (pointing at `verification_reports` /
     * `verification_items`).  Old columns are kept nullable so historical rows are untouched.
     */
    public function up(): void
    {
        Schema::table('master_data_part_price_logs', function (Blueprint $table) {
            // 1. Drop old FK constraints — columns stay, just lose the constraint
            $table->dropForeign(['report_id']);
            $table->dropForeign(['detail_id']);

            // 2. Add new columns pointing at the new tables
            $table->unsignedBigInteger('verification_report_id')->nullable()->after('report_id');
            $table->unsignedBigInteger('verification_item_id')->nullable()->after('detail_id');

            // 3. Add FK constraints for the new columns
            $table->foreign('verification_report_id')
                  ->references('id')->on('verification_reports')
                  ->nullOnDelete();

            $table->foreign('verification_item_id')
                  ->references('id')->on('verification_items')
                  ->nullOnDelete();

            // Note: report_id and detail_id remain as plain nullable int columns
            // so historical rows keep their data without needing a reports/details FK.
        });
    }

    public function down(): void
    {
        Schema::table('master_data_part_price_logs', function (Blueprint $table) {
            $table->dropForeign(['verification_report_id']);
            $table->dropForeign(['verification_item_id']);
            $table->dropColumn(['verification_report_id', 'verification_item_id']);

            // Restore original FKs
            $table->foreign('report_id')->references('id')->on('reports')->nullOnDelete();
            $table->foreign('detail_id')->references('id')->on('details')->nullOnDelete();
        });
    }
};
