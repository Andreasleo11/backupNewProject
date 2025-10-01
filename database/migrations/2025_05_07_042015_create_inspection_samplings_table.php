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
        Schema::create('inspection_samplings', function (Blueprint $table) {
            $table->id();
            $table->string('second_inspection_document_number');
            $table->integer('quantity');
            $table->string('box_label');
            $table->string('appearance');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_samplings');
    }
};
