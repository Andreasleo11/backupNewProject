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
        Schema::create('monthly_budget_report_summary_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('header_id');
            $table->string('name');
            $table->integer('dept_no');
            $table->integer('quantity');
            $table->string('uom');
            $table->string('supplier');
            $table->double('cost_per_unit');
            $table->string('remark');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_budget_report_summary_details');
    }
};
