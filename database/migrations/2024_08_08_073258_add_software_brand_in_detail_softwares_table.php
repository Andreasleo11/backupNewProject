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
        Schema::table("detail_softwares", function (Blueprint $table) {
            $table->string("software_brand")->after("software_id")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("detail_softwares", function (Blueprint $table) {
            $table->dropColumn("software_brand");
        });
    }
};
