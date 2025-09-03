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
        Schema::table("spk_remarks", function (Blueprint $table) {
            $table->boolean("is_revision")->default(0)->after("status");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("spk_remarks", function (Blueprint $table) {
            $table->dropColumn("is_revision");
        });
    }
};
