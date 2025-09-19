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
        Schema::table("inspection_packagings", function (Blueprint $table) {
            $table->string("remarks")->nullable()->after("judgement");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("inspection_packagings", function (Blueprint $table) {
            $table->dropColumn("remarks");
        });
    }
};
