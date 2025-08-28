<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("employee_daily_report_logs", function (Blueprint $table) {
            $table->id();
            $table->timestamp("logged_at")->useCurrent();
            $table->string("employee_id")->nullable();
            $table->string("employee_name")->nullable();
            $table->string("department_id")->nullable();
            $table->date("work_date")->nullable();
            $table->string("work_time")->nullable();
            $table->string("report_type")->nullable();
            $table->text("work_description")->nullable();
            $table->text("proof_url")->nullable();
            $table->string("status");
            $table->text("message")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("employee_daily_report_logs");
    }
};
