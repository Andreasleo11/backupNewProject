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
        Schema::create('header_form_overtime', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('dept');
            $table->date('create_date');
            $table->string('autograph_1')->nullable();
            $table->string('autograph_2')->nullable();
            $table->string('autograph_3')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('header_form_overtime');
    }
};
