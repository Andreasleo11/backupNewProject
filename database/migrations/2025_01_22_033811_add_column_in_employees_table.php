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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('Gender')->after('Nama');
            $table->string('Branch')->after('start_date');
            $table->string('employee_status')->after('Branch');
            $table->string('Grade')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('Gender');
            $table->dropColumn('Branch');
            $table->dropColumn('employee_status');
            $table->dropColumn('Grade');
        });
    }
};
