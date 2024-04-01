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
        Schema::create('monthly_pr', function (Blueprint $table) {
            $table->id();
            $table->string('month')->nullable();
            $table->string('year')->nullable();
            $table->string('autograph_1')->nullable();
            $table->string('autograph_2')->nullable();
            $table->string('autograph_3')->nullable();
            $table->string('autograph_user_1')->nullable();
            $table->string('autograph_user_2')->nullable();
            $table->string('autograph_user_3')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_pr');
    }
};
