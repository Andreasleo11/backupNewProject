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
        Schema::create("master_stock", function (Blueprint $table) {
            $table->id();
            $table->integer("stock_type_id");
            $table->integer("dept_id");
            $table->string("stock_code");
            $table->string("stock_description");
            $table->integer("stock_quantity")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("master_stock");
    }
};
