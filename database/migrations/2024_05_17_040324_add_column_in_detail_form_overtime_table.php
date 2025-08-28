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
        Schema::table("detail_form_overtime", function (Blueprint $table) {
            $table->integer("header_id")->after("id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("detail_form_overtime", function (Blueprint $table) {
            $table->dropColumn("header_id");
        });
    }
};
