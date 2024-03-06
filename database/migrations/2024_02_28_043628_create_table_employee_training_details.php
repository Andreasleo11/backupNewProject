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
        Schema::create('table_employee_training_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('header_id')->constrained();
            $table->string('training_name');
            $table->date('training_date');
            $table->boolean('is_internal')->nullable();
            $table->boolean('is_external')->nullable();
            $table->boolean('result')->nullable();
            $table->string('information');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_employee_training_details');
    }
};
