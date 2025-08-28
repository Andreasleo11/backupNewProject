<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("evaluation_datas", function (Blueprint $table) {
            DB::statement(
                'ALTER TABLE evaluation_datas CHANGE kerapian_pakaian kerapian_kerja VARCHAR(255) DEFAULT "C"',
            );

            $table->dropColumn("kerapian_rambut");
            $table->dropColumn("kerapian_sepatu");

            $table->string("perilaku_kerja")->nullable()->default("C")->after("loyalitas");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("evaluation_datas", function (Blueprint $table) {
            //

            DB::statement(
                "ALTER TABLE evaluation_datas CHANGE kerapian_kerja kerapian_pakaian VARCHAR(255)",
            );

            // Reverse dropping columns
            $table->string("kerapian_rambut")->nullable();
            $table->string("kerapian_sepatu")->nullable();

            // Drop the new column
            $table->dropColumn("perilaku_kerja");
        });
    }
};
