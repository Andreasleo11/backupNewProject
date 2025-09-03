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
            $table->string("kerajinan_kerja")->nullable()->default("C")->change();
            $table->string("kerapian_pakaian")->nullable()->default("C")->change();
            $table->string("kerapian_rambut")->nullable()->default("C")->change();
            $table->string("kerapian_sepatu")->nullable()->default("C")->change();
            $table->string("prestasi")->nullable()->default("C")->change();
            $table->string("loyalitas")->nullable()->default("C")->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("evaluation_datas", function (Blueprint $table) {
            //
        });
    }
};
