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
        Schema::table('inspection_quantities', function (Blueprint $table) {
            $table->integer('ng_sample_quantity')->after('sampling_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspection_quantities', function (Blueprint $table) {
            $table->dropColumn('ng_sample_quantity');
        });
    }
};
