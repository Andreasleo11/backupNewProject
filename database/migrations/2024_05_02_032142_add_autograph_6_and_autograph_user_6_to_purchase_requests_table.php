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
        Schema::table("purchase_requests", function (Blueprint $table) {
            $table->string("autograph_6")->nullable()->after("autograph_5");
            $table->string("autograph_user_6")->nullable()->after("autograph_user_5");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("purchase_requests", function (Blueprint $table) {
            $table->dropColumn("autograph_6");
            $table->dropColumn("autograph_user_6");
        });
    }
};
