<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add verification_report_id to header_form_adjusts for bridging adjust form to new VerificationReport.
     * Legacy records keep their report_id; new records use verification_report_id.
     */
    public function up(): void
    {
        Schema::table('header_form_adjusts', function (Blueprint $table) {
            $table->unsignedBigInteger('verification_report_id')->nullable()->after('report_id');
            // ponytail: no FK constraint — bridge column during transition
        });
    }

    public function down(): void
    {
        Schema::table('header_form_adjusts', function (Blueprint $table) {
            $table->dropColumn('verification_report_id');
        });
    }
};
