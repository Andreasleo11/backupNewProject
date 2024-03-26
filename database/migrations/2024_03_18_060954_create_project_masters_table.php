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
        Schema::create('project_masters', function (Blueprint $table) {
            $table->id();
            $table->string("project_name");
            $table->integer("dept");
            $table->date("request_date");
            $table->date("start_date")->nullable();
            $table->date("end_date")->nullable();
            $table->string("pic");
            $table->string("description");
            $table->string("status");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_masters');
    }
};
