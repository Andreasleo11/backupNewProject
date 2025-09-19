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
        Schema::table("inspection_measurements", function (Blueprint $table) {
            $table->decimal("actual_value", 8, 3)->after("limit_uom");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("inspection_measurements", function (Blueprint $table) {
            $table->dropColumn("actual_value");
        });
    }
};
