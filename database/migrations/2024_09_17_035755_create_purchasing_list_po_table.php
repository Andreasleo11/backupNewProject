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
        Schema::create("purchasing_list_po", function (Blueprint $table) {
            $table->id();
            $table->string("supplier_code");
            $table->string("supplier_name");
            $table->string("doc_status");
            $table->date("posting_date");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("purchasing_list_po");
    }
};
