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
        Schema::create('purchasing_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_code')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('p_member')->nullable();
            $table->string('persontocontact')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchasing_contacts');
    }
};
