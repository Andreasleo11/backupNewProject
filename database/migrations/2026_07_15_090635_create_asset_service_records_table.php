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
        Schema::create('asset_service_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->string('requested_by');
            $table->enum('action', ['replacement', 'installation', 'repair']);
            $table->enum('component_type', ['hardware', 'software']);
            $table->string('old_part')->nullable();
            $table->string('new_type_name')->nullable();
            $table->string('new_brand')->nullable();
            $table->string('new_name')->nullable();
            $table->string('new_serial_number')->nullable();
            $table->string('new_license')->nullable();
            $table->date('action_date')->nullable();
            $table->text('remark')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_service_records');
    }
};
