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
        Schema::create("inspection_measurements", function (Blueprint $table) {
            $table->id();
            $table->string("inspection_report_document_number");
            $table->double("lower_limit")->nullable();
            $table->double("upper_limit")->nullable();
            $table->string("limit_uom")->nullable();
            $table->datetime("start_datetime")->nullable();
            $table->datetime("end_datetime")->nullable();
            $table->string("judgement")->nullable();
            $table->string("part")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("inspection_measurements");
    }
};
