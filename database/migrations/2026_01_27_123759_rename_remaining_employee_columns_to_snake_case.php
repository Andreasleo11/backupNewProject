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
            $table->renameColumn('NIK', 'nik');
            $table->renameColumn('Gender', 'gender');
            $table->renameColumn('Branch', 'branch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'nik') && !Schema::hasColumn('employees', 'NIK')) {
                $table->renameColumn('nik', 'NIK');
            }
            if (Schema::hasColumn('employees', 'gender') && !Schema::hasColumn('employees', 'Gender')) {
                $table->renameColumn('gender', 'Gender');
            }
            if (Schema::hasColumn('employees', 'branch') && !Schema::hasColumn('employees', 'Branch')) {
                $table->renameColumn('branch', 'Branch');
            }
        });
    }
};
