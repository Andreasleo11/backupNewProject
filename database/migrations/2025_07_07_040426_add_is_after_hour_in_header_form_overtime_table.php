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
        Schema::table('header_form_overtime', function (Blueprint $table) {
            $table->boolean('is_after_hour')->default(true)->after('is_planned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('header_form_overtime', function (Blueprint $table) {
            $table->dropColumn('is_after_hour');
        });
    }
};
