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
        Schema::create("production_receipts", function (Blueprint $table) {
            $table->id();
            $table->date("post_date")->nullable();
            $table->string("item_code")->nullable();
            $table->string("item_name")->nullable();
            $table->integer("quantity")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("production_receipts");
    }
};
