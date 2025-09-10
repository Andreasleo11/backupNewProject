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
        Schema::table("details", function (Blueprint $table) {
            $table->dropColumn("prod_date");
            $table->dropColumn("shift");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("details", function (Blueprint $table) {
            $table->date("prod_date");
            $table->string("shift");
        });
    }
};
