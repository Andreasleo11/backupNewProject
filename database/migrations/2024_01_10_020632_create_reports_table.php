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
        Schema::create("reports", function (Blueprint $table) {
            $table->id();
            $table->date("rec_date");
            $table->date("verify_date");
            $table->string("customer");
            $table->string("invoice_no");
            $table->string("autograph_1")->nullable();
            $table->string("autograph_2")->nullable();
            $table->string("autograph_3")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("reports");
    }
};
