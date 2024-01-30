<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('autograph_user_1')->nullable();
            $table->string('autograph_user_2')->nullable();
            $table->string('autograph_user_3')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('autograph_user_1');
            $table->dropColumn('autograph_user_2');
            $table->dropColumn('autograph_user_3');
        });
    }
};
