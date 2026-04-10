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
        // Clean up legacy invalid dates that block schema changes
        DB::table('employees')->where('start_date', '0000-00-00')->update(['start_date' => null]);
        DB::table('employees')->where('end_date', '0000-00-00')->update(['end_date' => null]);
        DB::table('employees')->where('created_at', '0000-00-00 00:00:00')->update(['created_at' => null]);
        DB::table('employees')->where('updated_at', '0000-00-00 00:00:00')->update(['updated_at' => null]);

        Schema::table('employees', function (Blueprint $table) {
            $table->string('nik', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->char('nik', 5)->change();
        });
    }
};
