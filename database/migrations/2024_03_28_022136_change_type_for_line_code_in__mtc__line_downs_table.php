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
        Schema::table("mtc_line_downs", function (Blueprint $table) {
            $table->string("line_code")->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("mtc__line_downs", function (Blueprint $table) {
            $table->integer("line_code")->change();
        });
    }
};
