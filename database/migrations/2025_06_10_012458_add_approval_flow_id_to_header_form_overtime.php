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
        Schema::table('header_form_overtime', function (Blueprint $table) {
            $table->foreignId('approval_flow_id')->nullable()->constrained('approval_flows');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('header_form_overtime', function (Blueprint $table) {
            $table->dropForeign(['approval_flow_id']); // drop the FK constraint first
            $table->dropColumn('approval_flow_id'); // then drop the column
        });
    }
};
