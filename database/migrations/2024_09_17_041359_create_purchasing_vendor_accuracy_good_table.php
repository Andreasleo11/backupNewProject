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
        Schema::create("purchasing_vendor_accuracy_good", function (Blueprint $table) {
            $table->id();
            $table->string("vendor_code");
            $table->string("vendor_name");
            $table->string("item_code");
            $table->string("description");
            $table->string("delivery_no");
            $table->date("incoming_date");
            $table->integer("delivery_quantity");
            $table->integer("received_quantity");
            $table->integer("shortage_quantity");
            $table->integer("over_quantity");
            $table->string("close_status");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("purchasing_vendor_accuracy_good");
    }
};
