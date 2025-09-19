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
        Schema::table("purchase_order_download_logs", function (Blueprint $table) {
            $table->timestamp("created_at")->nullable(); // Adds 'created_at' column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("purchase_order_download_logs", function (Blueprint $table) {
            $table->dropColumn("created_at");
        });
    }
};
