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
        Schema::table('delivery_notes', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign('delivery_notes_approval_flow_id_foreign');

            // Then drop the column
            $table->dropColumn('approval_flow_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('approval_flow_id')->nullable()->after('status');
            $table
                ->foreign('approval_flow_id', 'delivery_notes_approval_flow_id_foreign')
                ->references('id')
                ->on('approval_flows')
                ->onDelete('cascade');
        });
    }
};
