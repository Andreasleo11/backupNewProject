<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_destinations', function (Blueprint $table) {
            // Step 1: Add new columns first
            $table->decimal('driver_cost', 15, 2)->nullable()->after('remarks');
            $table->decimal('kenek_cost', 15, 2)->nullable()->after('driver_cost');
            $table->decimal('balikan_cost', 15, 2)->nullable()->after('kenek_cost');
        });

        // Step 2: Copy old cost value into all three new fields
        DB::statement('UPDATE delivery_destinations SET driver_cost = cost, kenek_cost = cost, balikan_cost = cost');

        // Step 3: Drop old cost column
        Schema::table('delivery_destinations', function (Blueprint $table) {
            $table->dropColumn('cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Add back the 'cost' column
        Schema::table('delivery_destinations', function (Blueprint $table) {
            $table->decimal('cost', 15, 2)->nullable()->after('remarks');
        });

        // Step 2: Copy value back from 'driver_cost' to 'cost'
        DB::statement('UPDATE delivery_destinations SET cost = driver_cost');

        // Step 3: Drop the three new columns
        Schema::table('delivery_destinations', function (Blueprint $table) {
            $table->dropColumn('driver_cost');
            $table->dropColumn('kenek_cost');
            $table->dropColumn('balikan_cost');
        });
    }
};
