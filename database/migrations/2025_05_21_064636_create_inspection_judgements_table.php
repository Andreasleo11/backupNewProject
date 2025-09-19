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
        Schema::create("inspection_judgements", function (Blueprint $table) {
            $table->id();
            $table->string("detail_inspection_report_document_number");
            $table->integer("pass_quantity");
            $table->integer("reject_quantity");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("inspection_judgements");
    }
};
