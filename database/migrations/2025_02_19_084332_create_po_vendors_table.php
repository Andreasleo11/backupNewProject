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
        Schema::create('po_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_code');
            $table->string('vendor_name');
            $table->integer('group_code');
            $table->string('vendor_information_member')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('po_vendors');
    }
};
