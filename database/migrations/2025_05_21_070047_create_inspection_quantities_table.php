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
        Schema::create('inspection_quantities', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_report_document_number');
            $table->integer('output_quantity');
            $table->integer('pass_quantity');
            $table->integer('reject_quantity');
            $table->integer('sampling_quantity');
            $table->double('reject_rate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_quantities');
    }
};
