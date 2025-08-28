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
        Schema::table("header_form_overtime", function (Blueprint $table) {
            $table->string("autograph_4")->nullable();
            $table->boolean("is_approve")->nullable();
            $table->integer("status")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("header_form_overtime", function (Blueprint $table) {
            $table->dropColumn("autograph_4");
            $table->dropColumn("is_approve");
            $table->dropColumn("status");
        });
    }
};
