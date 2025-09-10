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
        Schema::table("inv_line_lists", function (Blueprint $table) {
            $table->boolean("jakarta")->nullable();
            $table->boolean("karawang")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("inv_line_list", function (Blueprint $table) {
            $table->dropColumn("jakarta");
            $table->dropColumn("karawang");
        });
    }
};
