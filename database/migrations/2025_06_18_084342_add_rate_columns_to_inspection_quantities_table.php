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
        Schema::table("inspection_quantities", function (Blueprint $table) {
            $table->double("pass_rate")->after("pass_quantity");
            $table->double("ng_sample_rate")->after("ng_sample_quantity");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("inspection_quantities", function (Blueprint $table) {
            //
        });
    }
};
