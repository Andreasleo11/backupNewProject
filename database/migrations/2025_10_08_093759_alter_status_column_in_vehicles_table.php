<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('status_new', 20)->default('active')->after('odometer');
        });

        DB::statement('UPDATE vehicles SET status_new = status');

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('status');         // drop old ENUM
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: create ENUM again if you really need to (or just string without SOLD)
        Schema::table('vehicles', function (Blueprint $table) {
            $table->enum('status', ['active', 'maintenance', 'retired'])->default('active');
        });
        DB::statement("UPDATE vehicles SET status = IF(status='sold','retired',status)");
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('status'); // drop string
        });
    }
};
