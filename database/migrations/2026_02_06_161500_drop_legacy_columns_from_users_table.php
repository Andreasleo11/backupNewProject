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
            // Note: We use array syntax for dropForeign to let Laravel handle the naming convention
            // or we can specify the exact constraint name if known. 
            // Given the previous migrations, let's try standard array syntax.
            // However, to be safe against "constraint does not exist" errors in some setups,
            // we might want to check, but standard migration rollback usually handles it.
            // Let's assume standard constraints.
            
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'specification_id']);
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
