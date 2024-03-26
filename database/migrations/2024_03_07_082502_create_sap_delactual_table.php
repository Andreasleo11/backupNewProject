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
        Schema::create('sap_delactual', function (Blueprint $table) {
            $table->string("item_no")->nullable();
            $table->date("delivery_date")->nullable();
            $table->string("item_name")->nullable();
            $table->integer("quantity")->nullable();
            $table->string("so_num")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_delactual');
    }
};
