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
        Schema::create("spk", function (Blueprint $table) {
            $table->id();
            $table->integer("spk_number")->nullable();
            $table->string("production_status")->nullable();
            $table->date("post_date")->nullable();
            $table->date("due_date")->nullable();
            $table->string("item_code")->nullable();
            $table->integer("plan_quantity")->nullable();
            $table->integer("complete_quantity")->nullable();
            $table->integer("outstanding_quantity")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("spk");
    }
};
