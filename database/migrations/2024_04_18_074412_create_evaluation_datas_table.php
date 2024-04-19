<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('evaluation_datas', function (Blueprint $table) {
            $table->id();
            $table->string("NIK");
            $table->date("Month");
            $table->integer("Alpha");
            $table->integer("Telat");
            $table->integer("Izin"); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_datas');
    }
};
