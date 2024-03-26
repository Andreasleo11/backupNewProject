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
        Schema::create('sap_delso', function (Blueprint $table) {
            $table->integer("doc_num")->nullable();
            $table->string("doc_status")->nullable();
            $table->string("item_no")->nullable();
            $table->integer("quantity")->nullable();
            $table->integer("delivered_qty")->nullable();
            $table->integer("line_num")->nullable();
            $table->string("row_status")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_delso');
    }
};
