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
        Schema::create('service_part_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->index();
            $table->string('name'); // e.g. Engine Oil, Brake Pads, Tire, General Inspection
            $table->unsignedInteger('default_interval_km')->nullable();
            $table->unsignedInteger('default_interval_days')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_part_catalog');
    }
};
