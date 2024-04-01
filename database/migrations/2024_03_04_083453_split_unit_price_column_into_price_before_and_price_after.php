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
        Schema::table('detail_purchase_requests', function (Blueprint $table) {
            $table->dropColumn('unit_price');
            $table->integer('price_before')->after('quantity');
            $table->integer('price')->after('price_before');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_purchase_requests', function (Blueprint $table) {
            $table->integer('unit_price')->after('quantity');
            $table->dropColumn('price_before');
            $table->dropColumn('price');
        });
    }
};
