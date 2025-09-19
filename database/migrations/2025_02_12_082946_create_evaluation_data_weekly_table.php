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
        Schema::create("evaluation_data_weekly", function (Blueprint $table) {
            $table->id();
            $table->string("NIK");
            $table->string("dept")->nullable();
            $table->date("month");
            $table->integer("Alpha")->default(0);
            $table->integer("Telat")->default(0);
            $table->integer("Izin")->default(0);
            $table->integer("Sakit")->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("evaluation_data_weekly");
    }
};
