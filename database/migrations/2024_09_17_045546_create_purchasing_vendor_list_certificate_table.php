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
        Schema::create("purchasing_vendor_list_certificate", function (Blueprint $table) {
            $table->id();
            $table->string("vendor_code");
            $table->string("vendor_name");
            $table->string("iso_9001_doc")->nullable();
            $table->date("iso_9001_start_date")->nullable();
            $table->date("iso_9001_end_date")->nullable();
            $table->string("iso_14001_doc")->nullable();
            $table->date("iso_14001_start_date")->nullable();
            $table->date("iso_14001_end_date")->nullable();
            $table->string("iatf_16949_doc")->nullable();
            $table->date("iatf_16949_start_date")->nullable();
            $table->date("iatf_16949_end_date")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("purchasing_vendor_list_certificate");
    }
};
