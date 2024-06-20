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
        Schema::create('monthly_budget_report_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('header_id');
            $table->string('name');
            $table->string('spec')->nullable();
            $table->string('uom');
            $table->integer('last_recorded_stock')->nullable();
            $table->string('usage_per_month')->nullable();
            $table->integer('quantity');
            $table->string('remark')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_budget_report_details');
    }
};
