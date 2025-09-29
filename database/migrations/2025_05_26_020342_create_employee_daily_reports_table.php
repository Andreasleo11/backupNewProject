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
        Schema::create('employee_daily_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('submitted_at');
            $table->string('report_type');
            $table->string('employee_id');
            $table->string('departement_id');
            $table->string('employee_name');
            $table->date('work_date');
            $table->string('work_time');
            $table->text('work_description');
            $table->text('proof_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_daily_reports');
    }
};
