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
        Schema::create('header_form_adjusts', function (Blueprint $table) {
            $table->id();
            $table->integer("report_id");
            $table->string("autograph_1")->nullable();
            $table->string("autograph_2")->nullable();
            $table->string("autograph_3")->nullable();
            $table->string("autograph_4")->nullable();
            $table->string("autograph_5")->nullable();
            $table->string("autograph_6")->nullable();
            $table->string("autograph_7")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('header_form_adjusts');
    }
};
