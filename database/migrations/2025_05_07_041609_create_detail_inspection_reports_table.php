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
        Schema::create('detail_inspection_reports', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_report_document_number');
            $table->string('document_number');
            $table->unsignedTinyInteger('quarter');
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_inspection_reports');
    }
};
