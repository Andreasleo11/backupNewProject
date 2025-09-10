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
        Schema::create("inventory_repair_histories", function (Blueprint $table) {
            $table->id();
            $table->integer("master_id");
            $table->string("request_name");
            $table->string("action");
            $table->string("type");
            $table->string("item_type");
            $table->string("item_brand");
            $table->string("item_name");
            $table->date("action_date")->nullable();
            $table->string("remark")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("inventory_repair_histories");
    }
};
