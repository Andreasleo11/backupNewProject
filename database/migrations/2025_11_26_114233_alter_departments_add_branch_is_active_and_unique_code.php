<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('branch', 50)->nullable()->after('code');
            $table->boolean('is_active')->default(true)->after('is_office');
            $table->string('dept_no', 10)->change()->unique();
            $table->string('name', 100)->change();
            $table->string('code', 10)->change()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropUnique('departments_code_unique');
            $table->dropUnique('departments_dept_no_unique');
            $table->dropColumn(['branch', 'is_active']);
        });
    }
};
