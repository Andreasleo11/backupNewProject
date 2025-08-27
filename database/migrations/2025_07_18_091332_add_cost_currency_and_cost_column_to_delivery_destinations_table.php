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
        Schema::table('delivery_destinations', function (Blueprint $table) {
            $table->double('cost')->nullable()->after('remarks');
            $table->string('cost_currency')->nullable()->after('cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_destinations', function (Blueprint $table) {
            $table->dropColumn('cost');
            $table->dropColumn('cost_currency');
        });
    }
};
