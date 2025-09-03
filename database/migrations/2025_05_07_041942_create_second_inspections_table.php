<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("second_inspections", function (Blueprint $table) {
            $table->id();
            $table->string("detail_inspection_report_document_number");
            $table->string("document_number");
            $table->integer("lot_size_quantity");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("second_inspections");
    }
};
