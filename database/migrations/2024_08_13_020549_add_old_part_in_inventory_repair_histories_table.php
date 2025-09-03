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
        Schema::table("inventory_repair_histories", function (Blueprint $table) {
            $table->string("old_part")->after("type")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("inventory_repair_histories", function (Blueprint $table) {
            $table->dropColumn("old_part");
        });
    }
};
