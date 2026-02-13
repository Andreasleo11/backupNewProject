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
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign keys first if they exist
            // Check if column exists first to act as a proxy for the constraint
            if (Schema::hasColumn('users', 'role_id')) {
                 try {
                    $table->dropForeign(['role_id']);
                 } catch (\Exception $e) {
                    // Ignore if FK doesn't exist
                 }
                 $table->dropColumn(['role_id']);
            }
            
            if (Schema::hasColumn('users', 'specification_id')) {
                $table->dropColumn(['specification_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable();
            $table->foreign('role_id')->references('id')->on('roles');
            $table->bigInteger('specification_id')->default(1);
        });
    }
};
