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
        Schema::create('delsched_stockwip', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->nullable();
			$table->integer('quantity')->nullable();
			$table->integer('total_after')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delsched_stockwip');
    }
};
