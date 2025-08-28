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
        Schema::create("forecast_customer_masters", function (Blueprint $table) {
            $table->id();
            $table->string("forecast_code");
            $table->string("forecast_name");
            $table->string("customer");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("forecast_customer_masters");
    }
};
