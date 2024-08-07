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
        Schema::create('header_maintenance_inventory_reports', function (Blueprint $table) {
            $table->id();
            $table->string('no_dokumen');
            $table->integer('master_id');
            $table->date('revision_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('header_maintenance_inventory_reports');
    }
};
