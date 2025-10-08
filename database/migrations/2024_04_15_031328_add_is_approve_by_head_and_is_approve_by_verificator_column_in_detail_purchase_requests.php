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
            $table->boolean('is_approve_by_head')->nullable()->after('updated_at');
            $table->boolean('is_approve_by_verificator')->nullable()->after('is_approve_by_head');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_purchase_requests', function (Blueprint $table) {
            $table->dropColumn('is_approve_by_head');
            $table->dropColumn('is_approve_by_verificator');
        });
    }
};
