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
        Schema::table("inspection_samplings", function (Blueprint $table) {
            $table->integer("ng_quantity")->nullable()->after("appearance");
            $table->string("remarks")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("inspection_samplings", function (Blueprint $table) {
            $table->dropColumn("ng_quantity");
            $table->dropColumn("remarks");
        });
    }
};
