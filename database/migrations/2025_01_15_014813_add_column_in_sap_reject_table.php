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
        Schema::table('sap_reject', function (Blueprint $table) {
            $table->string('item_description')->after('item_no');
            $table->renameColumn('warehouse', 'item_group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sap_reject', function (Blueprint $table) {
            $table->dropColumn('item_description');
            $table->renameColumn('item_group', 'warehouse');
        });
    }
};
