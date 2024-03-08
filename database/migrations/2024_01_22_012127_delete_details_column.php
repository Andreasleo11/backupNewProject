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
        Schema::table('details', function (Blueprint $table) {
            $table->dropColumn('remark_daijo');
            $table->dropColumn('remark_customer');
            $table->dropColumn('customer_defect');
            $table->dropColumn('daijo_defect');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
