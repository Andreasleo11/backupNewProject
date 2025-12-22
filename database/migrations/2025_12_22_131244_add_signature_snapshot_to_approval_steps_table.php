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
        Schema::table('approval_steps', function (Blueprint $table) {
            $table->unsignedBigInteger('user_signature_id')->nullable()->after('approver_id');
            $table->string('signature_image_path')->nullable()->after('user_signature_id');
            $table->string('signature_sha256', 64)->nullable()->after('signature_image_path');

            // Optional extras (uncomment if useful):
            // $table->json('signature_meta')->nullable()->after('signature_sha256');

            $table->foreign('user_signature_id')
                ->references('id')->on('user_signatures')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_steps', function (Blueprint $table) {
            $table->dropForeign(['user_signature_id']);
            $table->dropColumn(['user_signature_id', 'signature_image_path', 'signature_sha256']);
        });
    }
};
