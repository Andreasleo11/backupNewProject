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
        Schema::create('uti_holiday_list', function (Blueprint $table) {
            $table->id();
            $table->date("date")->nullable();
            $table->string("holiday_name")->nullable();
            $table->string("description")->nullable();
            $table->integer("half_day")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uti_holiday_list');
    }
};
