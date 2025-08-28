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
        Schema::create("prodplan_kri_linecaps", function (Blueprint $table) {
            $table->id();
            $table->date("running_date")->nullable();
            $table->string("line_code")->nullable();
            $table->integer("departement")->nullable();
            $table->decimal("time_limit_all", 12, 2)->nullable();
            $table->decimal("time_limit_one", 12, 2)->nullable();
            $table->decimal("time_limit_two", 12, 2)->nullable();
            $table->decimal("time_limit_three", 12, 2)->nullable();
            $table->string("runnning_part")->nullable();
            $table->integer("used_time")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("prodplan_kri_linecaps");
    }
};
