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
        Schema::create('sap_delsched', function (Blueprint $table) {
            $table->string("item_code");
            $table->date("delivery_date");
            $table->integer("delivery_qty");
            $table->string("so_number");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_delsched');
    }
};
