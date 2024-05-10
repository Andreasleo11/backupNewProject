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
        Schema::create('form_adjust_masters', function (Blueprint $table) {
            $table->id();
            $table->integer("detail_id");
            $table->string("rm_code");
            $table->string("rm_description");
            $table->integer("rm_quantity");
            $table->string("fg_measure");
            $table->string("rm_measure");
            $table->string("warehouse_name")->nullable();
            $table->string("remark")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_adjust_masters');
    }
};
