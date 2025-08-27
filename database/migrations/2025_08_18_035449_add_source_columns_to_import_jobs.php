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
        Schema::table('import_jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('import_jobs', 'source_disk')) $table->string('source_disk')->nullable();
            if (!Schema::hasColumn('import_jobs', 'source_path')) $table->string('source_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_jobs', function (Blueprint $table) {
            if (Schema::hasColumn('import_jobs', 'source_disk')) $table->dropColumn('source_disk');
            if (Schema::hasColumn('import_jobs', 'source_path')) $table->dropColumn('source_path');
        });
    }
};
