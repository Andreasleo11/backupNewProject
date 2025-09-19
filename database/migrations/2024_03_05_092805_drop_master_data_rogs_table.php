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
        Schema::table("master_data_rogs", function (Blueprint $table) {
            $table->drop("master_data_rogs");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create("master_data_rogs", function (Blueprint $table) {
            $table->id();
            $table->string("customer_name");
            $table->string("item_name");
            $table->timestamps();
        });
    }
};
