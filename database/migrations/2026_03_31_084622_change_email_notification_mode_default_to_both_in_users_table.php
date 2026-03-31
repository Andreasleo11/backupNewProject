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
            $table->string('email_notification_mode')->default('both')->change();
        });

        // Migrate users who were previously set to the old 'immediate' default
        \App\Infrastructure\Persistence\Eloquent\Models\User::where('email_notification_mode', 'immediate')
            ->update(['email_notification_mode' => 'both']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_notification_mode')->default('immediate')->change();
        });

        // Revert back to conventional immediate default
        \App\Infrastructure\Persistence\Eloquent\Models\User::where('email_notification_mode', 'both')
            ->update(['email_notification_mode' => 'immediate']);
    }
};
