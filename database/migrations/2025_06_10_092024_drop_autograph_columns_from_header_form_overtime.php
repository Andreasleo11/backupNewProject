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
            $table->dropColumn("autograph_1");
            $table->dropColumn("autograph_2");
            $table->dropColumn("autograph_3");
            $table->dropColumn("autograph_4");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("header_form_overtime", function (Blueprint $table) {
            $table->string("autograph_1")->after("branch");
            $table->string("autograph_2")->after("autograph_1");
            $table->string("autograph_3")->after("autograph_2");
            $table->string("autograph_4")->after("autograph_3");
        });
    }
};
