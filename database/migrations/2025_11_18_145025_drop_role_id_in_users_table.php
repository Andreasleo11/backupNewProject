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
        if (Schema::hasColumn('users', 'role_id')) {

            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $foreignKeys = $sm->listTableForeignKeys('users');

            foreach ($foreignKeys as $foreign) {
                if (in_array('role_id', $foreign->getLocalColumns())) {
                    Schema::table('users', function (Blueprint $table) use ($foreign) {
                        $table->dropForeign($foreign->getName());
                    });
                    break;
                }
            }

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->nullable()->after('remember_token');
        });
    }
};
