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
            $table->string('driver_cost_currency', 3)->nullable()->after('driver_cost');
            $table->string('kenek_cost_currency', 3)->nullable()->after('kenek_cost');
            $table->string('balikan_cost_currency', 3)->nullable()->after('balikan_cost');
        });

        // Copy old value
        DB::statement('UPDATE delivery_destinations SET 
            driver_cost_currency = cost_currency,
            kenek_cost_currency = cost_currency,
            balikan_cost_currency = cost_currency');

        // Now drop old column
        Schema::table('delivery_destinations', function (Blueprint $table) {
            $table->dropColumn('cost_currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_destinations', function (Blueprint $table) {
            $table->string('cost_currency', 3)->nullable()->after('balikan_cost_currency');
        });

        DB::statement('UPDATE delivery_destinations SET cost_currency = driver_cost_currency');

        Schema::table('delivery_destinations', function (Blueprint $table) {
            $table->dropColumn('driver_cost_currency');
            $table->dropColumn('kenek_cost_currency');
            $table->dropColumn('balikan_cost_currency');
        });
    }
};
