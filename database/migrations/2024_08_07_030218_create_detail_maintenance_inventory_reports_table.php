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
        Schema::create('detail_maintenance_inventory_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('header_id');
            $table->integer('category_id');
            $table->string('condition');
            $table->string('remark')->nullable();
            $table->string('checked_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_maintenance_inventory_reports');
    }
};
