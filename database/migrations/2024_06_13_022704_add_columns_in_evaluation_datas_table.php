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
            $table->string("pengawas")->nullable();
            $table->string("depthead")->nullable();
            $table->string("generalmanager")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("evaluation_datas", function (Blueprint $table) {
            $table->dropColumn("pengawas");
            $table->dropColumn("depthead");
            $table->dropColumn("generalmanager");
        });
    }
};
