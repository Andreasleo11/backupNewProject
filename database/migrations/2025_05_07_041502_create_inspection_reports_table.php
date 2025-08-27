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
        Schema::create('inspection_reports', function (Blueprint $table) {
            $table->id();
            $table->string('document_number');
            $table->string('customer');
            $table->date('inspection_date');
            $table->string('part_number');
            $table->string('part_name');
            $table->double('weight');
            $table->string('weight_uom');
            $table->string('material');
            $table->string('color');
            $table->string('tool_number_or_cav_number');
            $table->string('machine_number');
            $table->unsignedTinyInteger('shift');
            $table->string('operator')->nullable();
            $table->string('inspector_autograph')->nullable();
            $table->string('leader_autograph')->nullable();
            $table->string('head_autograph')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_reports');
    }
};
