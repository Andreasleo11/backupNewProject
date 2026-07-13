<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add adjust-form columns to verification_items so AdjustFormService can bridge from Detail → VerificationItem.
     */
    public function up(): void
    {
        Schema::table('verification_items', function (Blueprint $table) {
            $table->string('fg_measure')->nullable()->after('do_number');
            $table->string('fg_warehouse_name')->nullable()->after('fg_measure');
            $table->text('remark')->nullable()->after('fg_warehouse_name');
        });
    }

    public function down(): void
    {
        Schema::table('verification_items', function (Blueprint $table) {
            $table->dropColumn(['fg_measure', 'fg_warehouse_name', 'remark']);
        });
    }
};
