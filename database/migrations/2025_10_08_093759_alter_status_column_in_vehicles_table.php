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
        // Only proceed if status column exists and is not already a string
        if (Schema::hasColumn('vehicles', 'status')) {
            // Check if it's already a string, if so, skip
            $columnType = DB::select("SHOW COLUMNS FROM vehicles WHERE Field = 'status'")[0]->Type ?? '';
            
            if (strpos($columnType, 'enum') !== false) {
                // It's an ENUM, convert to string
                Schema::table('vehicles', function (Blueprint $table) {
                    $table->string('status_new', 20)->default('active')->after('odometer');
                });

                DB::statement('UPDATE vehicles SET status_new = status');

                Schema::table('vehicles', function (Blueprint $table) {
                    $table->dropColumn('status');
                });

                Schema::table('vehicles', function (Blueprint $table) {
                    $table->renameColumn('status_new', 'status');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only reverse if status is a string
        if (Schema::hasColumn('vehicles', 'status')) {
            $columnType = DB::select("SHOW COLUMNS FROM vehicles WHERE Field = 'status'")[0]->Type ?? '';
            
            if (strpos($columnType, 'varchar') !== false || strpos($columnType, 'char') !== false) {
                Schema::table('vehicles', function (Blueprint $table) {
                    $table->enum('status_temp', ['active', 'maintenance', 'retired'])->default('active');
                });
                
                DB::statement("UPDATE vehicles SET status_temp = IF(status='sold','retired',status)");
                
                Schema::table('vehicles', function (Blueprint $table) {
                    $table->dropColumn('status');
                });
                
                Schema::table('vehicles', function (Blueprint $table) {
                    $table->renameColumn('status_temp', 'status');
                });
            }
        }
    }
};
