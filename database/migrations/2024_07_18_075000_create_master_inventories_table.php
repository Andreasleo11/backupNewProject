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
        Schema::create('master_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->string('username');
            $table->string('dept');
            $table->string('type');
            $table->string('purpose')->nullable();
            $table->string('brand');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_inventories');
    }
};
