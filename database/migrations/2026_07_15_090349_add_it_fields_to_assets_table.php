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
        Schema::table('assets', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->after('notes');
            $table->string('username')->nullable()->after('ip_address');
            $table->string('purpose')->nullable()->after('username');
            $table->string('os')->nullable()->after('purpose');
            $table->string('position_image')->nullable()->after('os');
            $table->foreignId('department_id')->nullable()->after('position_image')
                  ->constrained('departments')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['ip_address', 'username', 'purpose', 'os', 'position_image', 'department_id']);
        });
    }
};
