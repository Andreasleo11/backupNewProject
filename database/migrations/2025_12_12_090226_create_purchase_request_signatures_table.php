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
        Schema::create('purchase_request_signatures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_request_id');
            $table->string('step_code', 50); // e.g. MAKER, DEPT_HEAD, HEAD_DESIGN, GM, PURCHASER, VERIFICATOR, DIRECTOR
            $table->unsignedBigInteger('signed_by_user_id')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
            $table->foreign('purchase_request_id')
                ->references('id')->on('purchase_requests')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_request_signatures');
    }
};
