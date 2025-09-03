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
        Schema::table("prodplan_scenarios", function (Blueprint $table) {
            $table->integer("val_int_kri")->nullable();
            $table->string("val_vc_kri")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("prodplan_scenarios", function (Blueprint $table) {
            $table->dropColumn("val_int_kri");
            $table->dropColumn("val_vc_kri");
        });
    }
};
