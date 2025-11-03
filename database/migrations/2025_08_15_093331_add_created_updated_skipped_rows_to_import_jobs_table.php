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
            $table->unsignedBigInteger('created_rows')->default(0)->after('processed_rows');
            $table->unsignedBigInteger('updated_rows')->default(0)->after('created_rows');
            $table->unsignedBigInteger('skipped_rows')->default(0)->after('updated_rows');
            $table->string('error_log_path')->nullable()->after('error');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_jobs', function (Blueprint $table) {
            $table->dropColumn('created_rows');
            $table->dropColumn('updated_rows');
            $table->dropColumn('skipped_rows');
            $table->dropColumn('error_log_path');
        });
    }
};
