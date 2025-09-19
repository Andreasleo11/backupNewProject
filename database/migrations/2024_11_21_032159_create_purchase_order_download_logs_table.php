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
        Schema::create("purchase_order_download_logs", function (Blueprint $table) {
            $table->id();
            $table->bigInteger("purchase_order_id");
            $table->bigInteger("user_id");
            $table->timestamp("last_downloaded_at");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("purchase_order_download_logs");
    }
};
