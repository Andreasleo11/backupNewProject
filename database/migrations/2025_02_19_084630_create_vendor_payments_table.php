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
        Schema::create("vendor_payments", function (Blueprint $table) {
            $table->id();
            $table->string("vendor_code");
            $table->string("payment_terms");
            $table->integer("payment_terms_final")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("vendor_payments");
    }
};
