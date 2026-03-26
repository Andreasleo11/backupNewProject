<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('employee_daily_report_logs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback supported for technical debt cleanup
    }
};
