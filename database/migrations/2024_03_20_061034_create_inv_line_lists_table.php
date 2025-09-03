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
        Schema::create("inv_line_lists", function (Blueprint $table) {
            $table->string("line_code")->primary();
            $table->string("line_name")->nullable();
            $table->string("category")->nullable();
            $table->string("area")->nullable();
            $table->integer("departement")->nullable();
            $table->integer("daily_minutes")->nullable();
            $table->integer("continue_running")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("inv_line_lists");
    }
};
