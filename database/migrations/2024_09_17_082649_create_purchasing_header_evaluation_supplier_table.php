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
        Schema::create("purchasing_header_evaluation_supplier", function (Blueprint $table) {
            $table->id();
            $table->string("doc_num");
            $table->string("vendor_code");
            $table->string("vendor_name");
            $table->integer("year");
            $table->string("grade")->nullable();
            $table->string("status")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("purchasing_header_evaluation_supplier");
    }
};
