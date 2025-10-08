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
        Schema::table('master_data_parts', function (Blueprint $table) {
            $table->unique('item_no');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_data_parts', function (Blueprint $table) {
            $table->dropColumn(['item_no']);
            $table->dropTimestamps();
        });
    }
};
