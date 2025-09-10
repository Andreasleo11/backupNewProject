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
        Schema::table("defects", function (Blueprint $table) {
            $table->boolean("is_daijo")->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("defects", function (Blueprint $table) {
            $table->boolean("is_daijo")->change();
        });
    }
};
