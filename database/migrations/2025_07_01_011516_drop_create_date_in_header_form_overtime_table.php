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
            $table->dropColumn('create_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('header_form_overtime', function (Blueprint $table) {
            $table->date('create_date')->after('dept_id');
        });
    }
};
