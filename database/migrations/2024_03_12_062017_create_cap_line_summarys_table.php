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
        Schema::create('cap_line_summarys', function (Blueprint $table) {
            $table->id();
            $table->integer('departement')->nullable();
            $table->string('line_category')->nullable();
            $table->integer('line_quantity')->nullable();
            $table->decimal('work_day', 5, 2)->nullable();
            $table->integer('ready_time')->nullable();
            $table->decimal('efficiency', 5, 2)->nullable();
            $table->integer('max_capacity')->nullable();
            $table->decimal('capacity_req_hour', 7, 2)->nullable();
            $table->integer('capacity_req_percent')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cap_line_summarys');
    }
};
