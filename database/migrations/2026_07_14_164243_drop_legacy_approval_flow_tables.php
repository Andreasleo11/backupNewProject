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
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('header_form_overtime') && Schema::hasColumn('header_form_overtime', 'approval_flow_id')) {
            $foreignKeys = array_map(fn($key) => $key['name'], Schema::getForeignKeys('header_form_overtime'));
            
            Schema::table('header_form_overtime', function (Blueprint $table) use ($foreignKeys) {
                if (in_array('header_form_overtime_approval_flow_id_foreign', $foreignKeys)) {
                    $table->dropForeign('header_form_overtime_approval_flow_id_foreign');
                }
                $table->dropColumn('approval_flow_id');
            });
        }

        if (Schema::hasTable('delivery_notes') && Schema::hasColumn('delivery_notes', 'approval_flow_id')) {
            $foreignKeys = array_map(fn($key) => $key['name'], Schema::getForeignKeys('delivery_notes'));
            
            Schema::table('delivery_notes', function (Blueprint $table) use ($foreignKeys) {
                if (in_array('delivery_notes_approval_flow_id_foreign', $foreignKeys)) {
                    $table->dropForeign('delivery_notes_approval_flow_id_foreign');
                }
                $table->dropColumn('approval_flow_id');
            });
        }

        Schema::dropIfExists('approval_flow_rules');
        Schema::dropIfExists('approval_flow_steps');
        Schema::dropIfExists('overtime_form_approvals');
        Schema::dropIfExists('approval_flows');

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a destructive cleanup migration. We do not recreate 
        // the obsolete legacy tables on rollback.
    }
};
