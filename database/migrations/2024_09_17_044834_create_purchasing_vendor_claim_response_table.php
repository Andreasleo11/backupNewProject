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
        Schema::create('purchasing_vendor_claim_response', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_claim_code')->nullable();
            $table->string('vendor_code');
            $table->string('vendor_name');
            $table->string('item_code');
            $table->string('description');
            $table->string('cpar_no');
            $table->date('cpar_sent_date');
            $table->date('cpar_response_date');
            $table->string('close_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchasing_vendor_claim_response');
    }
};
