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
        Schema::create('master_data_adjust', function (Blueprint $table) {
            $table->id();
            $table->string('fg_code')->nullable();
            $table->string('fg_name')->nullable();
            $table->string('fg_measure')->nullable();
            $table->string('rm_code')->nullable();
            $table->string('rm_description')->nullable();
            $table->float('rm_quantity')->nullable();
            $table->string('rm_measure')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_data_adjust');
    }
};
