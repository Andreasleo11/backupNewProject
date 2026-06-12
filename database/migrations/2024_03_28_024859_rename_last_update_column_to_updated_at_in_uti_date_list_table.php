<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('uti_date_list', function (Blueprint $table) {
            DB::statement('UPDATE `uti_date_list` SET `updated_at` = `last_update`');
            $table->dropColumn('last_update');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('uti_date_list', 'last_update')) {
            Schema::table('uti_date_list', function (Blueprint $table) {
                $table->timestamp('last_update')->nullable();
            });
        }

        try {
            DB::statement('UPDATE `uti_date_list` SET `last_update` = `updated_at`');
        } catch (\Exception $e) {
            // Ignore if columns are missing
        }

        if (Schema::hasColumn('uti_date_list', 'updated_at')) {
            Schema::table('uti_date_list', function (Blueprint $table) {
                $table->dropColumn('updated_at');
            });
        }
    }
};
