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
        Schema::create('delsched_solist', function (Blueprint $table) {
            $table->id();
            $table->string('so_number')->nullable();
			$table->string('so_status')->nullable();
			$table->string('item_code')->nullable();
			$table->integer('so_qty')->nullable();
			$table->integer('delivered_qty')->nullable();
			$table->string('row_status')->nullable();
			$table->string('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delsched_solist');
    }
};
