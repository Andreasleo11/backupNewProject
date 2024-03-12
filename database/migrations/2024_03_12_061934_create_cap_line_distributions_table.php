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
        Schema::create('cap_line_distributions', function (Blueprint $table) {
            $table->id();
            $table->string("line_code")->nullable();
            $table->string("item_code")->nullable();
            $table->integer("bom_level")->nullable();
            $table->integer("priority")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cap_line_distributions');
    }
};
