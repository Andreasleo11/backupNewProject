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
        Schema::create("first_inspections", function (Blueprint $table) {
            $table->id();
            $table->string("detail_inspection_report_document_number");
            $table->string("appearance");
            $table->double("weight");
            $table->string("weight_uom");
            $table->string("fitting_test")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("first_inspections");
    }
};
