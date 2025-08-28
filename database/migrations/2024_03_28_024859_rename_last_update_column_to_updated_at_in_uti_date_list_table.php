<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("uti_date_list", function (Blueprint $table) {
            DB::statement("UPDATE `uti_date_list` SET `updated_at` = `last_update`");
            $table->dropColumn("last_update");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("uti_date_list", function (Blueprint $table) {
            $table->timestamp("last_update")->nullable();
            DB::statement("UPDATE `uti_date_list` SET `last_update` = `updated_at`");
            $table->dropColumn("updated_at");
        });
    }
};
