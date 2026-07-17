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
        Schema::table('maintenance_report_details', function (Blueprint $table) {
            $table->string('custom_item_name')->nullable()->after('checklist_item_id');
            // We need to make checklist_item_id nullable in case it's a custom item
            // But doing so might require doctrine/dbal. Let's just drop the foreign key, change column, and add foreign key back if needed, or simply change it if the driver supports it.
            // Since it's Laravel 11, changing column is native.
            $table->unsignedBigInteger('checklist_item_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_report_details', function (Blueprint $table) {
            $table->dropColumn('custom_item_name');
            $table->unsignedBigInteger('checklist_item_id')->nullable(false)->change();
        });
    }
};
