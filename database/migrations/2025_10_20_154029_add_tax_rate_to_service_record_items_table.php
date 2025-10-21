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
        Schema::table('service_record_items', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(0)->after('discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_record_items', function (Blueprint $table) {
            $table->dropColumn('tax_rate');
        });
    }
};
