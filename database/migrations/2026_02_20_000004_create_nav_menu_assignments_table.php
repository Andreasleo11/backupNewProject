<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nav_menu_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('route_name', 120);
            $table->string('subject_type', 80);  // morphable class name
            $table->unsignedBigInteger('subject_id');
            $table->timestamps();

            $table->unique(['route_name', 'subject_type', 'subject_id']);
            $table->index(['subject_type', 'subject_id']);
            $table->index('route_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_menu_assignments');
    }
};
